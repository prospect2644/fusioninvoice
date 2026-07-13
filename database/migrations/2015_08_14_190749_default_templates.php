<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use FI\Modules\Settings\Models\Setting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class DefaultTemplates extends Migration
{
    public function up()
    {
        DB::table('clients')->whereNull('invoice_template')->update(['invoice_template' => Setting::getByKey('invoiceTemplate')]);
        DB::table('clients')->whereNull('quote_template')->update(['quote_template' => Setting::getByKey('quoteTemplate')]);

        $invoiceSubQuery = '(' . DB::table('clients')->select('invoice_template')->where('clients.id', DB::raw(DB::getTablePrefix() . 'invoices.id'))->toSql() . ')';
        $quoteSubQuery   = '(' . DB::table('clients')->select('quote_template')->where('clients.id', DB::raw(DB::getTablePrefix() . 'quotes.id'))->toSql() . ')';

        DB::table('invoices')->whereNull('template')->update(['template' => DB::raw($invoiceSubQuery)]);
        DB::table('quotes')->whereNull('template')->update(['template' => DB::raw($quoteSubQuery)]);
    }
}
