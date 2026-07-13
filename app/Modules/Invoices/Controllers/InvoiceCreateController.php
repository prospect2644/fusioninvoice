<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Modules\Invoices\Controllers;

use FI\Http\Controllers\Controller;
use FI\Modules\Clients\Models\Client;
use FI\Modules\CompanyProfiles\Models\CompanyProfile;
use FI\Modules\DocumentNumberSchemes\Models\DocumentNumberScheme;
use FI\Modules\Invoices\Models\Invoice;
use FI\Modules\Invoices\Requests\InvoiceStoreRequest;
use FI\Support\DateFormatter;

class InvoiceCreateController extends Controller
{
    public function create()
    {
        return view('invoices._modal_create')
            ->with('companyProfiles', CompanyProfile::getList())
            ->with('clients', Client::getDropDownList())
            ->with('documentNumberSchemes', DocumentNumberScheme::getListGroup());
    }

    public function store(InvoiceStoreRequest $request)
    {
        $input = $request->except('client_name');

        $client = Client::firstOrCreateByUniqueName($request->input('client_name'));
        if (false === $client)
        {
            return response()->json(['errors' => [[trans('fi.no_auth_to_create_client')]]], 403);
        }
        $input['client_id']    = $client->id;
        $input['invoice_date'] = DateFormatter::unformat($input['invoice_date']);

        $invoice = Invoice::create($input);

        return response()->json(['id' => $invoice->id], 200);
    }
}