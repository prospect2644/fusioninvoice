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

class ConvertStatuses extends Migration
{
    public function up()
    {
        Schema::table('invoices', function (Blueprint $table)
        {
            $table->enum('status', ['draft', 'sent', 'paid', 'canceled'])->after('invoice_date');
        });

        DB::table('invoices')->where('invoice_status_id', 1)->update(['status' => 'draft']);
        DB::table('invoices')->where('invoice_status_id', 2)->update(['status' => 'sent']);
        DB::table('invoices')->where('invoice_status_id', 3)->update(['status' => 'paid']);
        DB::table('invoices')->where('invoice_status_id', 4)->update(['status' => 'canceled']);

        Schema::table('quotes', function (Blueprint $table)
        {
            $table->enum('status', ['draft', 'sent', 'approved', 'rejected', 'canceled'])->after('quote_date');
        });

        DB::table('quotes')->where('quote_status_id', 1)->update(['status' => 'draft']);
        DB::table('quotes')->where('quote_status_id', 2)->update(['status' => 'sent']);
        DB::table('quotes')->where('quote_status_id', 3)->update(['status' => 'approved']);
        DB::table('quotes')->where('quote_status_id', 4)->update(['status' => 'rejected']);
        DB::table('quotes')->where('quote_status_id', 5)->update(['status' => 'canceled']);

        Schema::table('invoices', function (Blueprint $table)
        {
            $table->dropColumn(['invoice_status_id']);
        });

        Schema::table('quotes', function (Blueprint $table)
        {
            $table->dropColumn(['quote_status_id']);
        });
    }
}
