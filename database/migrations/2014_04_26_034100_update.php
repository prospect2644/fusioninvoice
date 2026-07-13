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

class Update extends Migration
{
    public function up()
    {
        DB::table('invoices')->whereNotIn('id', function ($query)
        {
            $query->select('invoice_id')->from('invoice_amounts');
        })->delete();

        DB::table('quotes')->whereNotIn('id', function ($query)
        {
            $query->select('invoice_id')->from('quote_amounts');
        })->delete();
    }
}