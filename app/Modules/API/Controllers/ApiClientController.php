<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Modules\API\Controllers;

use Exception;
use FI\Modules\API\Requests\APIClientCustomFieldsRequest;
use FI\Modules\API\Requests\APIClientDeleteRequest;
use FI\Modules\API\Requests\APIClientShowRequest;
use FI\Modules\API\Requests\APIClientUpdateRequest;
use FI\Modules\Clients\Models\Client;
use FI\Modules\CustomFields\Models\ClientCustom;
use FI\Modules\Clients\Requests\ClientStoreRequest;
use FI\Modules\Users\Models\User;
use Illuminate\Http\Request;

class ApiClientController extends ApiController
{
    public function buildFailedValidationResponse(array $errors)
    {
        return response()->json(['success' => false, 'message' => $errors], 422);
    }

    public function index(Request $request)
    {
        try
        {
            if ($request->has('paginated_response') && $request->get('paginated_response') == 1)
            {
                $clients = Client::getSelect()->orderBy('name')->customField($request->get('include_custom_fields'))->paginate(config('fi.resultsPerPage'));
            }
            else
            {
                $clients = Client::getSelect()->orderBy('name')->customField($request->get('include_custom_fields'))->get();
            }

            return response()->json(['success' => true, 'message' => trans('fi.record_successfully_retrieved'), 'data' => $clients], 200);

        }
        catch (Exception $e)
        {
            return response()->json(['success' => false, 'message' => trans('fi.record_not_found')], 400);
        }

    }

    public function show($id, Request $request)
    {
        try
        {

            $client = Client::getSelect()->customField($request->get('include_custom_fields'))->find($id);

            if ($client)
            {
                return response()->json(['success' => true, 'message' => trans('fi.record_successfully_retrieved'), 'data' => $client], 200);
            }
            else
            {
                return response()->json(['success' => false, 'message' => trans('fi.record_not_found')], 400);
            }

        }
        catch (Exception $e)
        {
            return response()->json(['success' => false, 'message' => trans('fi.record_not_found')], 400);
        }

    }

    public function store(ClientStoreRequest $request)
    {
        try
        {

            $client = Client::create($request->except('password', 'password_confirmation', 'allow_client_center_login'));

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

            return response()->json(['success' => true, 'message' => trans('fi.record_successfully_created')], 201);

        }
        catch (Exception $e)
        {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function update($id, APIClientUpdateRequest $request)
    {
        try
        {

            $client = Client::getSelect()->find($id);
            if (!$client)
            {
                return response()->json(['success' => false, 'message' => trans('fi.record_not_found')], 400);
            }
            $client->fill($request->except('password', 'password_confirmation', 'allow_client_center_login'));
            $client->save();

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

            return response()->json(['success' => true, 'message' => trans('fi.record_successfully_updated'), 'data' => $client], 200);

        }
        catch (Exception $e)
        {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }

    }

    public function delete($id)
    {

        try
        {
            $client = Client::find($id);

            if ($client)
            {
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
                        'success'  => false,
                        'messages' => $error,
                    ], 400);
                }
                $client->delete();

                return response()->json(['success' => true, 'message' => trans('fi.record_successfully_deleted')], 200);
            }
            else
            {
                return response()->json(['success' => false, 'message' => trans('fi.record_not_found')], 400);
            }

        }
        catch (Exception $e)
        {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }

    }

    public function addUpdateCustomFields(APIClientCustomFieldsRequest $request)
    {

        try
        {

            $input = $request->except('client_id');

            ClientCustom::updateOrCreate(['client_id' => request('client_id')], $input);

            $client = Client::getSelect()->with('custom')->find(request('client_id'));

            return response()->json(['success' => true, 'message' => trans('fi.record_successfully_created'), 'data' => $client], 200);

        }
        catch (Exception $e)
        {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }

    }
}