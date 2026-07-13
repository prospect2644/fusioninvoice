<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Modules\Clients\Controllers;

use Carbon\Carbon;
use DB;
use FI\Http\Controllers\Controller;
use FI\Modules\Addons\Models\Addon;
use FI\Modules\Clients\Models\Client;
use FI\Modules\Clients\Requests\ClientStoreRequest;
use FI\Modules\Clients\Requests\ClientUpdateRequest;
use FI\Modules\Countries\Models\Country;
use FI\Modules\CustomFields\Models\ClientCustom;
use FI\Modules\CustomFields\Support\CustomFieldsParser;
use FI\Modules\CustomFields\Support\CustomFieldsTransformer;
use FI\Modules\Mru\Events\MruLog;
use FI\Modules\Payments\Models\Payment;
use FI\Modules\Tags\Models\Tag;
use FI\Modules\TaskList\Models\Task;
use FI\Modules\Transitions\Models\Transitions;
use FI\Modules\Users\Models\User;
use FI\Support\Frequency;
use FI\Traits\ReturnUrl;
use Illuminate\Support\Facades\Storage;

class ClientController extends Controller
{
    use ReturnUrl;

    public function index()
    {
        $this->setReturnUrl();

        $tags             = json_decode(request('tags', '')) ?? [];
        $tagsMustMatchAll = request('tagsMustMatchAll', 0);

        $clients = Client::getSelect()
            ->leftJoin('clients_custom', 'clients_custom.client_id', '=', 'clients.id')
            ->with(['currency'])
            ->sortable(['name' => 'asc'])
            ->status(request('status'))
            ->type(request('type'))
            ->keywords(request('search'))
            ->tags($tags, $tagsMustMatchAll)
            ->paginate(config('fi.resultsPerPage'));

        return view('clients.index')
            ->with('clients', $clients)
            ->with('searchPlaceholder', trans('fi.search_clients'))
            ->with('types', ['' => trans('fi.show_all_types')] + Client::getTypesList())
            ->with('statuses', ['' => trans('fi.show_all_statuses')] + Client::getStatusList())
            ->with('tags', $tags)
            ->with('tagsMustMatchAll', $tagsMustMatchAll);
    }

    public function create()
    {
        return view('clients.form')
            ->with('selectedTab', request('tab', 'general'))
            ->with('editMode', false)
            ->with('customFields', CustomFieldsParser::getFields('clients'))
            ->with('tags', Tag::whereTagEntity('client')->pluck('name', 'name'))
            ->with('selectedTags', [])
            ->with('countries', Country::getAll())
            ->with('clientTitle', ['' => trans('fi.select_client_title')] + Client::getClientTitle())
            ->with('parentClients', ['' => trans('fi.select_parent_client')] + Client::getParentClients());
    }

    public function store(ClientStoreRequest $request)
    {
        $input            = $request->except('custom', 'tags', 'cname', 'cemail', 'password', 'password_confirmation', 'allow_client_center_login');
        $input['user_id'] = auth()->user()->id;

        $client = Client::create($input);

        // Save the custom fields.
        $customFieldData = CustomFieldsTransformer::transform(request('custom', []), 'clients', $client);
        $client->custom->update($customFieldData);

        $tags    = $request->input('tags', []);
        $tag_ids = [];

        foreach ($tags as $tag)
        {
            $tag = Tag::firstOrNew(['name' => $tag, 'tag_entity' => 'client'])->fill(['name' => $tag, 'tag_entity' => 'client']);

            $tag->save();

            $tag_ids[] = $tag->id;
        }

        foreach ($tag_ids as $tag_id)
        {
            $client->tags()->create(['client_id' => $client->id, 'tag_id' => $tag_id]);
        }

        // If client center login allowed and password inserted then need to create user with client
        if ($request->get('allow_client_center_login') == 1)
        {
            $password = $request->get('password');
            if (!$password)
            {
                return redirect()->route('clients.edit', [$client->id])
                    ->with('error', trans('fi.client_password_required'));
            }

            $user = new User(['email' => $client->email, 'name' => $client->name, 'user_type' => 'client']);

            $user->password  = $password;
            $user->client_id = $client->id;

            $user->save();

        }

        return redirect()->route('clients.show', [$client->id])
            ->with('alertSuccess', trans('fi.record_successfully_created'));
    }

    public function show($clientId)
    {

        $this->setReturnUrl();

        $client = Client::with(['tags.tag', 'contacts'])->find($clientId);

        event(new MruLog(['module' => 'clients', 'action' => 'view', 'id' => $clientId, 'title' => $client->name]));

        $invoices = $client->invoices()
            ->select('invoices.*',
                DB::raw("(SELECT COUNT(credit_memo.id) FROM " . DB::getTablePrefix() . "invoices as credit_memo
                        WHERE credit_memo.type = 'credit_memo' AND credit_memo.status != 'applied' AND credit_memo.client_id = " . DB::getTablePrefix() . "invoices.client_id) as count_credit_memo"
                ),
                DB::raw("(SELECT COUNT(" . DB::getTablePrefix() . "payments.id) FROM " . DB::getTablePrefix() . "payments
                        WHERE " . DB::getTablePrefix() . "payments.client_id = " . DB::getTablePrefix() . "invoices.client_id AND " . DB::getTablePrefix() . "payments.remaining_balance > 0) as count_pre_payment"
                ),
                DB::raw("(SELECT COUNT(open_invoice.id) FROM " . DB::getTablePrefix() . "invoices as open_invoice
                        WHERE open_invoice.type = 'invoice' AND open_invoice.status IN ('sent','draft') AND open_invoice.client_id = " . DB::getTablePrefix() . "invoices.client_id) as count_sent_invoices"
                )
            )
            ->with(['client', 'activities', 'amount.invoice.currency'])
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->take(config('fi.resultsPerPage'))->get();

        $quotes = $client->quotes()
            ->with(['client', 'activities', 'amount.quote.currency', 'invoice'])
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->take(config('fi.resultsPerPage'))->get();

        $recurringInvoices = $client->recurringInvoices()
            ->with(['client', 'amount.recurringInvoice.currency'])
            ->orderBy('next_date', 'desc')
            ->orderBy('id', 'desc')
            ->take(config('fi.resultsPerPage'))->get();

        $filterUsers = [];
        if (auth()->user()->user_type == 'admin')
        {
            $users = User::select('id', 'name')->get()->toArray();
            foreach ($users as $user)
            {
                $filterUsers[$user['id']] = $user['name'];
            }
        }

        return view('clients.view')
            ->with('client', $client)
            ->with('invoicePaymentSummary', $client->currencyWiseSummary())
            ->with('selectedTab', request('tab', 'general'))
            ->with('invoices', $invoices)
            ->with('quotes', $quotes)
            ->with('payments', Payment::clientId($clientId)->whereNull('credit_memo_id')->orderBy('created_at', 'desc')->get())
            ->with('recurringInvoices', $recurringInvoices)
            ->with('customFields', CustomFieldsParser::getFields('clients'))
            ->with('frequencies', Frequency::lists())
            ->with('childClients', Client::getChildClients($clientId))
            ->with('tags', [])
            ->with('modules', Transitions::getModulesList())
            ->with('filterUsers', $filterUsers)
            ->with('tagsMustMatchAll', 0)
            ->with('tasks', Task::whereClientId($clientId)->get())
            ->with('containerAddonStatus', Addon::getContainersAddonStatus())
            ->with('typeLabels', ['lead' => 'label-warning', 'prospect' => 'label-danger', 'customer' => 'label-success', 'affiliate' => 'label-info']);
    }

    public function invoiceSummary($id, $currency_code)
    {
        $client = Client::find($id);
        return view('clients.summary')
            ->with('currency', $currency_code)
            ->with('invoicePaymentSummary', $client->currencyWiseSummary());
    }

    public function edit($clientId)
    {

        $client = Client::getSelect()->with(['custom', 'tags.tag'])->find($clientId);

        event(new MruLog(['module' => 'clients', 'action' => 'edit', 'id' => $clientId, 'title' => $client->name]));

        $selectedTags = [];

        foreach ($client->tags as $tagDetail)
        {
            $selectedTags[] = $tagDetail->tag->name;
        }

        return view('clients.form')
            ->with('editMode', true)
            ->with('client', $client)
            ->with('selectedTab', request('tab', 'general'))
            ->with('customFields', CustomFieldsParser::getFields('clients'))
            ->with('tags', Tag::whereTagEntity('client')->pluck('name', 'name'))
            ->with('typeLabels', ['lead' => 'label-warning', 'prospect' => 'label-danger', 'customer' => 'label-success', 'affiliate' => 'label-info'])
            ->with('selectedTags', $selectedTags)
            ->with('parentClients', ['' => trans('fi.select_parent_client')] + Client::getParentClients($clientId))
            ->with('countries', Country::getAll())
            ->with('returnUrl', $this->getReturnUrl());

    }

    public function update(ClientUpdateRequest $request, $id)
    {
        /** @var Client $client */
        $client = Client::find($id);
        $client->fill($request->except('custom', 'tags', 'cname', 'cemail', 'password', 'password_confirmation', 'allow_client_center_login', 'tab'));
        $client->save();

        // Save the custom fields.
        $customFieldData = CustomFieldsTransformer::transform(request('custom', []), 'clients', $client);
        $client->custom->update($customFieldData);

        $tags    = $request->input('tags', []);
        $tag_ids = [];

        foreach ($tags as $tag)
        {
            $tag = Tag::firstOrNew(['name' => $tag, 'tag_entity' => 'client'])->fill(['name' => $tag, 'tag_entity' => 'client']);

            $tag->save();

            $tag_ids[] = $tag->id;
        }

        $client->deleteTags($client);

        foreach ($tag_ids as $tag_id)
        {
            $client->tags()->insert(['client_id' => $client->id, 'tag_id' => $tag_id, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);
        }

        // If client center login allowed and password inserted then we have to upsert entry on user table
        $allowClientCenterLogin = $request->get('allow_client_center_login', '');
        $password               = $request->get('password');
        if ($allowClientCenterLogin == 1)
        {
            if (isset($client->user))
            {
                $user = User::find($client->user->id);
                $user->fill(['email' => $client->email, 'name' => $client->name]);
                if ($password)
                {
                    $user->password = $password;
                }
                $user->save();
            }
            else
            {
                if (!$password)
                {
                    return redirect()->back()
                        ->with('error', trans('fi.client_password_required'));
                }
                $user            = new User(['email' => $client->email, 'name' => $client->name, 'user_type' => 'client']);
                $user->password  = $password;
                $user->client_id = $client->id;
                $user->save();

            }
        }

        if ($allowClientCenterLogin == '' && isset($client->user))
        {
            User::find($client->user->id)->delete();
        }

        return redirect()->route('clients.show', [$id, 'tab' => request('tab', 'general')])
            ->with('selectedTab', request('tab', 'general'))
            ->with('alertSuccess', trans('fi.record_successfully_updated'));
    }

    public function delete($clientId)
    {
        $client = Client::find($clientId);

        $module   = [];
        $module[] = $client->payments->count() > 0 ? trans('fi.payments') : '';
        $module[] = $client->notes->count() > 0 ? trans('fi.notes') : '';
        $module[] = $client->expenses->count() > 0 ? trans('fi.expenses') : '';
        $module[] = $client->tasks->count() > 0 ? trans('fi.tasks') : '';
        $module[] = $client->quotes->count() > 0 ? trans('fi.quotes') : '';
        $module[] = $client->recurringInvoices->count() > 0 ? trans('fi.recurring_invoices') : '';
        $module[] = $client->invoices->count() > 0 ? trans('fi.invoices') : '';
        $module   = array_filter($module);

        if (count($module) > 0)
        {
            $error = trans('fi.client_related_record_exist', ['modules' => implode(', ', $module)]);
            return response()->json([
                'success' => false,
                'errors'  => ['messages' => [$error]],
            ], 400);
        }

        $client->delete();

        return response()->json([
            'success' => true,
            'message' => trans('fi.record_successfully_deleted'),
        ], 200);
    }

    public function ajaxLookup()
    {
        $clients = Client::select('unique_name')
            ->where('active', 1)
            ->where('unique_name', 'like', '%' . request('query') . '%')
            ->orderBy('unique_name')
            ->get();

        $list = [];

        foreach ($clients as $client)
        {
            $list[]['value'] = $client->unique_name;
        }

        return json_encode($list);
    }

    public function ajaxModalEdit()
    {
        $client       = Client::getSelect()->with(['custom', 'tags.tag'])->find(request('client_id'));
        $selectedTags = [];

        foreach ($client->tags as $tagDetail)
        {
            $selectedTags[] = $tagDetail->tag->name;
        }

        return view('clients._modal_edit')
            ->with('editMode', true)
            ->with('client', $client)
            ->with('refreshToRoute', request('refresh_to_route'))
            ->with('id', request('id'))
            ->with('customFields', CustomFieldsParser::getFields('clients'))
            ->with('tags', Tag::whereTagEntity('client')->pluck('name', 'name'))
            ->with('countries', Country::getAll())
            ->with('selectedTags', $selectedTags);
    }

    public function ajaxModalUpdate(ClientUpdateRequest $request, $id)
    {
        $client = Client::find($id);
        $client->fill($request->except('custom', 'tags'));
        $client->save();

        // Save the custom fields.
        $customFieldData = CustomFieldsTransformer::transform(request('custom', []), 'clients', $client);
        $client->custom->update($customFieldData);

        $tags    = $request->input('tags', []);
        $tag_ids = [];

        foreach ($tags as $tag)
        {
            $tag = Tag::firstOrNew(['name' => $tag], ['tag_entity' => 'client'])->fill(['name' => $tag, 'tag_entity' => 'client']);

            $tag->save();

            $tag_ids[] = $tag->id;
        }

        $client->deleteTags($client);

        foreach ($tag_ids as $tag_id)
        {
            $client->tags()->insert(['client_id' => $client->id, 'tag_id' => $tag_id, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);
        }

        return response()->json(['success' => true], 200);
    }

    public function ajaxModalLookup()
    {
        return view('clients._modal_lookup')
            ->with('updateClientIdRoute', request('update_client_id_route'))
            ->with('refreshToRoute', request('refresh_to_route'))
            ->with('clients', Client::getDropDownList())
            ->with('client', Client::whereId(request('client_id'))->first())
            ->with('id', request('id'));
    }

    public function ajaxCheckName()
    {
        $client = Client::select('id')->where('unique_name', request('client_name'))->first();

        if ($client)
        {
            return response()->json(['success' => true, 'client_id' => $client->id], 200);
        }

        return response()->json([
            'success' => false,
            'errors'  => ['messages' => [trans('fi.client_not_found')]],
        ], 400);
    }

    public function ajaxCheckDuplicateName()
    {
        if (Client::where(function ($query)
            {
                $query->where('name', request('client_name'));
                $query->orWhere('unique_name', request('unique_name'));
            })->where('id', '<>', request('client_id'))->count() > 0
        )
        {
            return response()->json(['is_duplicate' => 1]);
        }

        return response()->json(['is_duplicate' => 0]);
    }

    public function deleteImage($id, $columnName)
    {
        $customFields = ClientCustom::whereClientId($id)->first();

        $existingFile = 'clients' . DIRECTORY_SEPARATOR . $customFields->{$columnName};

        if (Storage::disk(CustomFieldsTransformer::STORAGE_DISK_NAME)->exists($existingFile))
        {
            try
            {
                Storage::disk(CustomFieldsTransformer::STORAGE_DISK_NAME)->delete($existingFile);

                $customFields->{$columnName} = null;
                $customFields->save();
            }
            catch (\Exception $e)
            {

            }
        }
    }

    public function showFilterTags()
    {
        $resultsPerPage   = 10;
        $selectedTags     = json_decode(request('tags', '[]'));
        $tagsMustMatchAll = request('tagsMustMatchAll', 0);

        $checkedTags = Tag::where('tag_entity', '=', 'client')
            ->whereIn('id', $selectedTags)->get();
        $allTags     = Tag::where('tag_entity', '=', 'client')
            ->whereNotIn('id', $selectedTags)
            ->paginate($resultsPerPage);

        $nextPageCount = $resultsPerPage;
        if (($allTags->total() - ($allTags->currentPage() * $resultsPerPage)) < $resultsPerPage)
        {
            $nextPageCount = $allTags->total() - ($allTags->currentPage() * $resultsPerPage);
        }

        $nextPageLink = '';
        if ($allTags->hasMorePages())
        {
            $params       = [
                'tags'             => json_encode($selectedTags),
                'tagsMustMatchAll' => $tagsMustMatchAll,
            ];
            $nextPageLink = $allTags->appends($params)->nextPageUrl();
        }

        if (request('firstLoad'))
        {
            return view('clients._modal_filter_tags')
                ->with('selectedTags', $selectedTags)
                ->with('tagsMustMatchAll', $tagsMustMatchAll)
                ->with('nextPageLink', $nextPageLink)
                ->with('nextPageCount', $nextPageCount)
                ->with('checkedTags', $checkedTags)
                ->with('allTags', $allTags)
                ->with('hasNoTags', ((count($allTags) + count($checkedTags)) <= 0));
        }
        else
        {
            return response()->json([
                'html'          => view('clients._filter_tags_list')
                    ->with('selectedTags', $selectedTags)
                    ->with('tagsMustMatchAll', $tagsMustMatchAll)
                    ->with('checkedTags', $checkedTags)
                    ->with('allTags', $allTags)->render(),
                'link'          => $nextPageLink,
                'nextPageCount' => $nextPageCount,
            ]);
        }
    }

    public function emailPaymentReceiptStatus($clientId)
    {
        $client   = Client::find($clientId);
        $response = ['email_receipt' => false, 'currency_code' => $client->currency_code];
        switch ($client->automatic_email_payment_receipt)
        {
            case 'yes':
                $response['email_receipt'] = true;
                break;
            case 'no':
                $response['email_receipt'] = false;
                break;
            case 'default':
                $response['email_receipt'] = config('fi.automaticEmailPaymentReceipts');
                break;
            default:
                $response['email_receipt'] = false;
        }
        return $response;
    }
}
