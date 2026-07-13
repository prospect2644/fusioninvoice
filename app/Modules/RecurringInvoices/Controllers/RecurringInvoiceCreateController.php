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
use FI\Modules\Clients\Models\Client;
use FI\Modules\CompanyProfiles\Models\CompanyProfile;
use FI\Modules\DocumentNumberSchemes\Models\DocumentNumberScheme;
use FI\Modules\RecurringInvoices\Models\RecurringInvoice;
use FI\Modules\RecurringInvoices\Requests\RecurringInvoiceStoreRequest;
use FI\Support\DateFormatter;
use FI\Support\Frequency;

class RecurringInvoiceCreateController extends Controller
{
    public function create()
    {
        return view('recurring_invoices._modal_create')
            ->with('companyProfiles', CompanyProfile::getList())
            ->with('documentNumberSchemes', DocumentNumberScheme::getList())
            ->with('clients', Client::getDropDownList())
            ->with('frequencies', Frequency::lists());
    }

    public function store(RecurringInvoiceStoreRequest $request)
    {
        $input = $request->except('client_name');

        $client = Client::firstOrCreateByUniqueName($request->input('client_name'));
        if (false === $client)
        {
            return response()->json(['errors' => [[trans('fi.no_auth_to_create_client')]]], 403);
        }
        $input['client_id'] = $client->id;

        $input['next_date'] = DateFormatter::unformat($input['next_date']);
        $input['stop_date'] = ($input['stop_date']) ? DateFormatter::unformat($input['stop_date']) : '0000-00-00';

        $recurringInvoice = RecurringInvoice::create($input);

        return response()->json(['success' => true, 'url' => route('recurringInvoices.edit', [$recurringInvoice->id])], 200);
    }
}