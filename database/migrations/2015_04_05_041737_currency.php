<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class Currency extends Migration
{
    public function up()
    {
        $baseCurrency = DB::table('settings')->where('setting_key', 'baseCurrency')->first()->setting_value;
        // There may be some records with null currency_code values so we need to update these
        DB::table('clients')->whereNull('currency_code')->update(['currency_code' => $baseCurrency]);

        $invoices = DB::table('invoices')
                      ->select('invoices.id as invoice_id', 'clients.id as client_id', 'clients.currency_code')
                      ->join('clients','invoices.client_id','=','clients.id')
                      ->whereNull('invoices.currency_code')
                      ->get();
        foreach ($invoices as $invoice)
        {
            DB::table('invoices')->where('id', $invoice->invoice_id)->update(['currency_code' => $invoice->currency_code]);
        }

        $quotes = DB::table('quotes')
                    ->select('quotes.id as quote_id', 'clients.id as client_id', 'clients.currency_code')
                    ->join('clients','quotes.client_id','=','clients.id')
                    ->whereNull('quotes.currency_code')
                    ->get();
        foreach ($quotes as $quote)
        {
            DB::table('quotes')->where('id', $quote->quote_id)->update(['currency_code' => $quote->currency_code]);
        }
    }
}
