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
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class InvoiceCreateDate extends Migration
{
    public function up()
    {
        Schema::table('invoices', function (Blueprint $table)
        {
            $table->date('invoice_date')->after('updated_at');
        });

        DB::table('invoices')->update(['invoice_date' => DB::raw('created_at')]);
    }
}