<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Modules\Quotes\Models;

use FI\Modules\Currencies\Support\CurrencyConverterFactory;
use FI\Modules\CustomFields\Models\QuoteCustom;
use FI\Modules\DocumentNumberSchemes\Models\DocumentNumberScheme;
use FI\Modules\Mru\Models\Mru;
use FI\Modules\Quotes\Events\AddTransition;
use FI\Modules\Quotes\Support\QuoteCalculate;
use FI\Support\DateFormatter;
use Illuminate\Support\Str;

class QuoteObserver
{
    public function created(Quote $quote)
    {
        // Create the empty quote amount record
        $quoteCalculate = new QuoteCalculate();
        $quoteCalculate->calculate($quote);

        // Increment the next id
        DocumentNumberScheme::incrementNextId($quote);

        // Create the custom quote record.
        $quote->custom()->save(new QuoteCustom());

        event(new AddTransition($quote, 'created'));
    }

    public function creating(Quote $quote)
    {
        if (!$quote->user_id)
        {
            $quote->user_id = auth()->user()->id;
        }

        if (!$quote->quote_date)
        {
            $quote->quote_date = date('Y-m-d');
        }

        if (!$quote->expires_at)
        {
            $quote->expires_at = DateFormatter::incrementDateByDays($quote->quote_date->format('Y-m-d'), config('fi.quotesExpireAfter'));
        }

        if (!$quote->company_profile_id)
        {
            $quote->company_profile_id = config('fi.defaultCompanyProfile');
        }

        if (!$quote->document_number_scheme_id)
        {
            $quote->document_number_scheme_id = config('fi.quoteGroup');
        }

        if (!$quote->number)
        {
            $quote->number = DocumentNumberScheme::generateNumber($quote->document_number_scheme_id, $quote->client->invoice_prefix);
        }

        if (!isset($quote->terms))
        {
            $quote->terms = config('fi.quoteTerms');
        }

        if (!isset($quote->footer))
        {
            $quote->footer = config('fi.quoteFooter');
        }

        if (!$quote->status)
        {
            $quote->status = 'draft';
        }

        if (!$quote->currency_code)
        {
            $quote->currency_code = $quote->client->currency_code;
        }

        if (!$quote->template)
        {
            $quote->template = $quote->companyProfile->quote_template;
        }

        if ($quote->currency_code == config('fi.baseCurrency'))
        {
            $quote->exchange_rate = 1;
        }
        else if (!$quote->exchange_rate)
        {
            $currencyConverter    = CurrencyConverterFactory::create();
            $quote->exchange_rate = $currencyConverter->convert(config('fi.baseCurrency'), $quote->currency_code);
        }

        $quote->url_key = Str::random(32);
    }

    public function deleted(Quote $quote)
    {
        foreach ($quote->items as $item)
        {
            $item->delete();
        }

        foreach ($quote->activities as $activity)
        {
            $activity->delete();
        }

        foreach ($quote->mailQueue as $mailQueue)
        {
            $mailQueue->delete();
        }

        foreach ($quote->notes as $note)
        {
            $note->delete();
        }

        $quote->custom()->delete();
        $quote->amount()->delete();

        $documentNumberScheme = DocumentNumberScheme::where('id', $quote->document_number_scheme_id)
            ->where('last_number', $quote->number)
            ->first();

        if ($documentNumberScheme)
        {
            $documentNumberScheme->next_id = $documentNumberScheme->next_id - 1;
            $documentNumberScheme->save();
        }

        Mru::whereUserId(auth()->user()->id)->whereModule('quotes')->whereElementId($quote->id)->delete();
    }

}