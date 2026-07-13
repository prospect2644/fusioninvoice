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

class FixTemplates extends Migration
{
    public function up()
    {
        DB::table('clients')->whereNull('invoice_template')->orWhere('invoice_template', '')->update(['invoice_template' => config('fi.invoiceTemplate')]);
        DB::table('clients')->whereNull('quote_template')->orWhere('quote_template', '')->update(['quote_template' => config('fi.quoteTemplate')]);
        DB::table('invoices')->whereNull('template')->orWhere('template', '')->update(['template' => config('fi.invoiceTemplate')]);
        DB::table('quotes')->whereNull('template')->orWhere('template', '')->update(['template' => config('fi.quoteTemplate')]);
    }
}
