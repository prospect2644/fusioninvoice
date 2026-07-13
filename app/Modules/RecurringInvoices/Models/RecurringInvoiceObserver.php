<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Modules\RecurringInvoices\Models;

use FI\Modules\Currencies\Support\CurrencyConverterFactory;
use FI\Modules\CustomFields\Models\RecurringInvoiceCustom;
use FI\Modules\Mru\Models\Mru;
use FI\Modules\RecurringInvoices\Events\AddTransition;
use FI\Modules\RecurringInvoices\Support\RecurringInvoiceCalculate;

class RecurringInvoiceObserver
{
    public function created(RecurringInvoice $recurringInvoice)
    {
        // Create the empty invoice amount record.
        $recurringInvoiceCalculate = new RecurringInvoiceCalculate();
        $recurringInvoiceCalculate->calculate($recurringInvoice->id);

        // Create the custom record.
        $recurringInvoice->custom()->save(new RecurringInvoiceCustom());

        event(new AddTransition($recurringInvoice, 'created'));

        // Update the client's status to customer
        $client       = $recurringInvoice->client;
        $client->type = 'customer';
        $client->save();
    }

    public function creating(RecurringInvoice $recurringInvoice)
    {
        if (!$recurringInvoice->user_id)
        {
            $recurringInvoice->user_id = auth()->user()->id;
        }

        if (!$recurringInvoice->company_profile_id)
        {
            $recurringInvoice->company_profile_id = config('fi.defaultCompanyProfile');
        }

        if (!$recurringInvoice->document_number_scheme_id)
        {
            $recurringInvoice->document_number_scheme_id = config('fi.invoiceGroup');
        }

        if (!isset($recurringInvoice->terms))
        {
            $recurringInvoice->terms = config('fi.invoiceTerms');
        }

        if (!isset($recurringInvoice->footer))
        {
            $recurringInvoice->footer = config('fi.invoiceFooter');
        }

        if (!$recurringInvoice->template)
        {
            $recurringInvoice->template = $recurringInvoice->companyProfile->invoice_template;
        }

        if (!$recurringInvoice->currency_code)
        {
            $recurringInvoice->currency_code = $recurringInvoice->client->currency_code;
        }

        if ($recurringInvoice->currency_code == config('fi.baseCurrency'))
        {
            $recurringInvoice->exchange_rate = 1;
        }
        elseif (!$recurringInvoice->exchange_rate)
        {
            $currencyConverter               = CurrencyConverterFactory::create();
            $recurringInvoice->exchange_rate = $currencyConverter->convert(config('fi.baseCurrency'), $recurringInvoice->currency_code);
        }
    }

    public function deleted(RecurringInvoice $recurringInvoice)
    {
        foreach ($recurringInvoice->items as $item)
        {
            $item->delete();
        }

        $recurringInvoice->amount()->delete();
        $recurringInvoice->custom()->delete();

        Mru::whereUserId(auth()->user()->id)->whereModule('recurring_invoices')->whereElementId($recurringInvoice->id)->delete();
    }
}