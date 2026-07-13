<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Modules\RecurringInvoices\Controllers;

use FI\Http\Controllers\Controller;
use FI\Modules\CompanyProfiles\Models\CompanyProfile;
use FI\Modules\RecurringInvoices\Events\AddTransition;
use FI\Modules\RecurringInvoices\Models\RecurringInvoice;
use FI\Modules\Tags\Models\Tag;
use FI\Support\Frequency;
use FI\Traits\ReturnUrl;

class RecurringInvoiceController extends Controller
{
    use ReturnUrl;

    public function index()
    {
        $this->setReturnUrl();

        $tags             = json_decode(request('tags', '')) ?? [];
        $tagsMustMatchAll = request('tagsMustMatchAll', 0);

        $recurringInvoices = RecurringInvoice::select('recurring_invoices.*')
            ->join('clients', 'clients.id', '=', 'recurring_invoices.client_id')
            ->join('recurring_invoice_amounts', 'recurring_invoice_amounts.recurring_invoice_id', '=', 'recurring_invoices.id')
            ->leftJoin('recurring_invoices_custom', 'recurring_invoices_custom.recurring_invoice_id', '=', 'recurring_invoices.id')
            ->with(['client', 'activities', 'amount.recurringInvoice.currency'])
            ->keywords(request('search'))
            ->clientId(request('client'))
            ->status(request('status'))
            ->companyProfileId(request('company_profile'))
            ->sortable(['next_date' => 'desc', 'id' => 'desc'])
            ->tags($tags, $tagsMustMatchAll)
            ->paginate(config('fi.resultsPerPage'));

        return view('recurring_invoices.index')
            ->with('recurringInvoices', $recurringInvoices)
            ->with('searchPlaceholder', trans('fi.search_recurring_invoices'))
            ->with('frequencies', Frequency::lists())
            ->with('statuses', ['all_statuses' => trans('fi.all_statuses'), 'active' => trans('fi.active'), 'inactive' => trans('fi.inactive')])
            ->with('companyProfiles', ['' => trans('fi.all_company_profiles')] + CompanyProfile::getList())
            ->with('tags', $tags)
            ->with('tagsMustMatchAll', $tagsMustMatchAll);
    }

    public function showFilterTags()
    {
        $resultsPerPage   = 10;
        $selectedTags     = json_decode(request('tags', '[]'));
        $tagsMustMatchAll = request('tagsMustMatchAll', 0);

        $checkedTags = Tag::where('tag_entity', '=', 'sales')
            ->whereIn('id', $selectedTags)->get();
        $allTags     = Tag::where('tag_entity', '=', 'sales')
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
            return view('recurring_invoices._modal_filter_tags')
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
                'html'          => view('invoices._filter_tags_list')
                    ->with('selectedTags', $selectedTags)
                    ->with('tagsMustMatchAll', $tagsMustMatchAll)
                    ->with('checkedTags', $checkedTags)
                    ->with('allTags', $allTags)->render(),
                'link'          => $nextPageLink,
                'nextPageCount' => $nextPageCount,
            ]);
        }
    }

    public function delete($id)
    {
        $recurringInvoice = RecurringInvoice::find($id);

        event(new AddTransition($recurringInvoice, 'deleted'));

        $recurringInvoice->delete();

        return redirect()->route('recurringInvoices.index')
            ->with('alert', trans('fi.record_successfully_deleted'));
    }
}