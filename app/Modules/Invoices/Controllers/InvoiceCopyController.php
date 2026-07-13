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
use FI\Modules\Attachments\Models\Attachment;
use FI\Modules\Clients\Models\Client;
use FI\Modules\CompanyProfiles\Models\CompanyProfile;
use FI\Modules\DocumentNumberSchemes\Models\DocumentNumberScheme;
use FI\Modules\Invoices\Models\Invoice;
use FI\Modules\Invoices\Models\InvoiceItem;
use FI\Modules\Invoices\Requests\InvoiceStoreRequest;
use FI\Support\DateFormatter;

class InvoiceCopyController extends Controller
{
    public function create()
    {
        $invoice = Invoice::find(request('invoice_id'));

        return view('invoices._modal_copy')
            ->with('invoice', $invoice)
            ->with('documentNumberSchemes', DocumentNumberScheme::getList())
            ->with('companyProfiles', CompanyProfile::getList())
            ->with('invoice_date', DateFormatter::format())
            ->with('clients', Client::getDropDownList())
            ->with('user_id', auth()->user()->id);
    }

    public function store(InvoiceStoreRequest $request)
    {
        $client = Client::firstOrCreateByUniqueName($request->input('client_name'));
        if (false === $client)
        {
            return response()->json(['errors' => [[trans('fi.no_auth_to_create_client')]]], 403);
        }

        $fromInvoice = Invoice::find($request->input('invoice_id'));

        $toInvoice = Invoice::create([
            'client_id'                 => $client->id,
            'company_profile_id'        => $request->input('company_profile_id'),
            'invoice_date'              => DateFormatter::unformat(request('invoice_date')),
            'document_number_scheme_id' => $request->input('document_number_scheme_id'),
            'currency_code'             => $fromInvoice->currency_code,
            'exchange_rate'             => $fromInvoice->exchange_rate,
            'terms'                     => $fromInvoice->terms,
            'footer'                    => $fromInvoice->footer,
            'template'                  => $fromInvoice->template,
            'summary'                   => $fromInvoice->summary,
            'discount'                  => $fromInvoice->discount,
        ]);

        foreach ($fromInvoice->items as $item)
        {
            InvoiceItem::create([
                'invoice_id'    => $toInvoice->id,
                'name'          => $item->name,
                'description'   => $item->description,
                'quantity'      => $item->quantity,
                'price'         => $item->price,
                'tax_rate_id'   => $item->tax_rate_id,
                'tax_rate_2_id' => $item->tax_rate_2_id,
                'display_order' => $item->display_order,
            ]);
        }

        // Copy the custom fields
        $custom = collect($fromInvoice->custom)->except('invoice_id')->toArray();
        $toInvoice->custom->update($custom);

        // Copy attachments
        foreach ($fromInvoice->attachments as $attachment)
        {
            Attachment::create([
                'user_id'           => $attachment->user_id,
                'attachable_id'     => $toInvoice->id,
                'attachable_type'   => $attachment->attachable_type,
                'filename'          => $attachment->filename,
                'mimetype'          => $attachment->mimetype,
                'size'              => $attachment->size,
                'client_visibility' => $attachment->client_visibility,
                'content'           => $attachment->content,
            ]);
        }

        return response()->json(['id' => $toInvoice->id], 200);
    }
}