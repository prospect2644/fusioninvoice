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

class RecurringInvoices extends Migration
{
    public function up()
    {
        Schema::table('recurring_invoices', function (Blueprint $table)
        {
            $table->renameColumn('generate_at', 'old_generate_at');
        });

        Schema::table('recurring_invoices', function (Blueprint $table)
        {
            $table->date('generate_at')->default('0000-00-00');
            $table->date('stop_at')->default('0000-00-00');
        });

        DB::table('recurring_invoices')->update(['generate_at' => DB::raw('old_generate_at')]);

        Schema::table('recurring_invoices', function (Blueprint $table)
        {
            $table->dropColumn('old_generate_at');
        });
    }
}
