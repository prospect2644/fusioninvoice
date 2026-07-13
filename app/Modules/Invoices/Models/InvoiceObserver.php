<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Modules\Invoices\Models;

use FI\Modules\Currencies\Support\CurrencyConverterFactory;
use FI\Modules\CustomFields\Models\InvoiceCustom;
use FI\Modules\Expenses\Models\Expense;
use FI\Modules\DocumentNumberSchemes\Models\DocumentNumberScheme;
use FI\Modules\Invoices\Events\AddTransition;
use FI\Modules\Invoices\Support\InvoiceCalculate;
use FI\Modules\Mru\Models\Mru;
use FI\Modules\Payments\Models\Payment;
use FI\Modules\Quotes\Models\Quote;
use FI\Support\DateFormatter;
use Illuminate\Support\Str;

class InvoiceObserver
{
    public function created(Invoice $invoice)
    {
        // Create the empty invoice amount record.
        $invoiceCalculate = new InvoiceCalculate();
        $invoiceCalculate->calculate($invoice);

        // Increment the next id.
        DocumentNumberScheme::incrementNextId($invoice);

        // Create the custom invoice record.
        $invoice->custom()->save(new InvoiceCustom());

        // Update the client's status to customer
        $client       = $invoice->client;
        $client->type = 'customer';
        $client->save();

        if ($invoice->type == 'credit_memo')
        {
            event(new AddTransition($invoice, 'credit_memo_created'));
        }
        else
        {
            if ($invoice->recurring_invoice_id)
            {
                event(new AddTransition($invoice, 'created_from_recurring'));
            }
            else
            {
                event(new AddTransition($invoice, 'created'));
            }
        }
    }

    public function creating(Invoice $invoice)
    {
        if (!$invoice->user_id)
        {
            $invoice->user_id = auth()->user()->id;
        }

        if (!$invoice->invoice_date)
        {
            $invoice->invoice_date = date('Y-m-d');
        }

        if (!$invoice->due_at)
        {
            if ($invoice->type == 'credit_memo')
            {
                $invoice->due_at = DateFormatter::incrementDate($invoice->invoice_date->format('Y-m-d'), 4, 1);
            }
            else
            {
                $invoice->due_at = DateFormatter::incrementDateByDays($invoice->invoice_date->format('Y-m-d'), config('fi.invoicesDueAfter'));
            }
        }

        if (!$invoice->company_profile_id)
        {
            $invoice->company_profile_id = config('fi.defaultCompanyProfile');
        }

        if (!$invoice->document_number_scheme_id)
        {
            $invoice->document_number_scheme_id = config('fi.invoiceGroup');
        }

        if (!$invoice->number)
        {
            $invoice->number = DocumentNumberScheme::generateNumber($invoice->document_number_scheme_id, $invoice->client->invoice_prefix);
        }

        if (!isset($invoice->terms))
        {
            $invoice->terms = config('fi.invoiceTerms');
        }

        if (!isset($invoice->footer))
        {
            $invoice->footer = config('fi.invoiceFooter');
        }

        if (!$invoice->status)
        {
            $invoice->status = 'draft';
        }

        if (!$invoice->currency_code)
        {
            $invoice->currency_code = $invoice->client->currency_code;
        }

        if (!$invoice->template)
        {
            $invoice->template = $invoice->companyProfile->invoice_template;
        }

        if ($invoice->currency_code == config('fi.baseCurrency'))
        {
            $invoice->exchange_rate = 1;
        }
        else if (!$invoice->exchange_rate)
        {
            $currencyConverter      = CurrencyConverterFactory::create();
            $invoice->exchange_rate = $currencyConverter->convert(config('fi.baseCurrency'), $invoice->currency_code);
        }

        $invoice->url_key = Str::random(32);
    }

    public function deleted(Invoice $invoice)
    {
        foreach ($invoice->items as $item)
        {
            $item->delete();
        }

        foreach ($invoice->payments as $payment)
        {
            $payment->delete();
        }

        foreach ($invoice->activities as $activity)
        {
            $activity->delete();
        }

        foreach ($invoice->mailQueue as $mailQueue)
        {
            $mailQueue->delete();
        }

        foreach ($invoice->notes as $note)
        {
            $note->delete();
        }

        $invoice->custom()->delete();
        $invoice->amount()->delete();

        if ($invoice->type == 'credit_memo')
        {
            $payments = Payment::with('paymentInvoice')->whereCreditMemoId($invoice->id)->get();
            foreach ($payments as $payment)
            {
                foreach ($payment->paymentInvoice as $paymentInvoice)
                {
                    $paymentInvoice->delete();
                }
            }
        }

        Quote::where('invoice_id', $invoice->id)->update(['invoice_id' => 0]);

        Expense::where('invoice_id', $invoice->id)->update(['invoice_id' => 0]);

        $documentNumberScheme = DocumentNumberScheme::where('id', $invoice->document_number_scheme_id)
            ->where('last_number', $invoice->number)
            ->first();

        if ($documentNumberScheme)
        {
            $documentNumberScheme->next_id = $documentNumberScheme->next_id - 1;
            $documentNumberScheme->save();
        }

        Mru::whereUserId(auth()->user()->id)->whereModule('invoices')->whereElementId($invoice->id)->delete();
    }
}