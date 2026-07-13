<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Modules\Quotes\Support;

use FI\Modules\CustomFields\Models\CustomField;
use FI\Modules\DocumentNumberSchemes\Models\DocumentNumberScheme;
use FI\Modules\Invoices\Events\InvoiceModified;
use FI\Modules\Invoices\Models\Invoice;
use FI\Modules\Invoices\Models\InvoiceItem;

class QuoteToInvoice
{
    public function convert($quote, $invoiceDate, $dueAt, $documentNumberSchemeId)
    {
        $record = [
            'client_id'                 => $quote->client_id,
            'invoice_date'              => $invoiceDate,
            'due_at'                    => $dueAt,
            'document_number_scheme_id' => $documentNumberSchemeId,
            'number'                    => DocumentNumberScheme::generateNumber($documentNumberSchemeId, $quote->client->invoice_prefix),
            'user_id'                   => $quote->user_id,
            'status'                    => 'draft',
            'terms'                     => ((config('fi.convertQuoteTerms') == 'quote') ? $quote->terms : config('fi.invoiceTerms')),
            'footer'                    => $quote->footer,
            'currency_code'             => $quote->currency_code,
            'exchange_rate'             => $quote->exchange_rate,
            'summary'                   => $quote->summary,
            'discount'                  => $quote->discount,
            'company_profile_id'        => $quote->company_profile_id,
        ];

        $toInvoice = Invoice::create($record);

        CustomField::copyCustomFieldValues($quote, $toInvoice);

        $quote->invoice_id = $toInvoice->id;
        $quote->status     = 'approved';
        $quote->save();

        foreach ($quote->quoteItems as $item)
        {
            $itemRecord = [
                'invoice_id'    => $toInvoice->id,
                'name'          => $item->name,
                'description'   => $item->description,
                'quantity'      => $item->quantity,
                'price'         => $item->price,
                'tax_rate_id'   => $item->tax_rate_id,
                'tax_rate_2_id' => $item->tax_rate_2_id,
                'display_order' => $item->display_order,
            ];

            InvoiceItem::create($itemRecord);
        }

        event(new InvoiceModified($toInvoice));

        return $toInvoice;
    }
}