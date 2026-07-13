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
use FI\Modules\RecurringInvoices\Models\RecurringInvoiceItem;
use FI\Modules\RecurringInvoices\Requests\RecurringInvoiceStoreRequest;
use FI\Support\DateFormatter;
use FI\Support\Frequency;

class RecurringInvoiceCopyController extends Controller
{
    public function create()
    {
        return view('recurring_invoices._modal_copy')
            ->with('recurringInvoice', RecurringInvoice::find(request('recurring_invoice_id')))
            ->with('documentNumberSchemes', DocumentNumberScheme::getList())
            ->with('companyProfiles', CompanyProfile::getList())
            ->with('clients', Client::getDropDownList())
            ->with('frequencies', Frequency::lists());
    }

    public function store(RecurringInvoiceStoreRequest $request)
    {
        $client = Client::firstOrCreateByUniqueName($request->input('client_name'));
        if (false === $client)
        {
            return response()->json(['errors' => [[trans('fi.no_auth_to_create_client')]]], 403);
        }
        $input['client_id'] = $client->id;

        $fromRecurringInvoice = RecurringInvoice::find($request->input('recurring_invoice_id'));

        $toRecurringInvoice = RecurringInvoice::create([
            'client_id'                 => $client->id,
            'company_profile_id'        => $request->input('company_profile_id'),
            'document_number_scheme_id' => $request->input('document_number_scheme_id'),
            'currency_code'             => $fromRecurringInvoice->currency_code,
            'exchange_rate'             => $fromRecurringInvoice->exchange_rate,
            'terms'                     => $fromRecurringInvoice->terms,
            'footer'                    => $fromRecurringInvoice->footer,
            'template'                  => $fromRecurringInvoice->template,
            'summary'                   => $fromRecurringInvoice->summary,
            'discount'                  => $fromRecurringInvoice->discount,
            'recurring_frequency'       => $request->input('recurring_frequency'),
            'recurring_period'          => $request->input('recurring_period'),
            'next_date'                 => DateFormatter::unformat($request->input('next_date')),
            'stop_date'                 => ($request->input('stop_date') ? DateFormatter::unformat($request->input('stop_date')) : '0000-00-00'),
        ]);

        foreach ($fromRecurringInvoice->items as $item)
        {
            RecurringInvoiceItem::create([
                'recurring_invoice_id' => $toRecurringInvoice->id,
                'name'                 => $item->name,
                'description'          => $item->description,
                'quantity'             => $item->quantity,
                'price'                => $item->price,
                'tax_rate_id'          => $item->tax_rate_id,
                'tax_rate_2_id'        => $item->tax_rate_2_id,
                'display_order'        => $item->display_order,
            ]);
        }

        // Copy the custom fields
        $custom = collect($fromRecurringInvoice->custom)->except('recurring_invoice_id')->toArray();
        $toRecurringInvoice->custom->update($custom);

        return response()->json(['success' => true, 'url' => route('recurringInvoices.edit', [$toRecurringInvoice->id])], 200);
    }
}