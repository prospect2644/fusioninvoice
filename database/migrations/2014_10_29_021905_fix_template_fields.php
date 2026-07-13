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

class FixTemplateFields extends Migration
{
    public function up()
    {
        Schema::table('clients', function (Blueprint $table)
        {
            $table->renameColumn('invoice_template', 'old_invoice_template');
        });

        Schema::table('clients', function (Blueprint $table)
        {
            $table->renameColumn('quote_template', 'old_quote_template');
        });

        Schema::table('clients', function (Blueprint $table)
        {
            $table->string('invoice_template')->nullable();
            $table->string('quote_template')->nullable();
        });

        Schema::table('invoices', function (Blueprint $table)
        {
            $table->renameColumn('template', 'old_template');
        });

        Schema::table('invoices', function (Blueprint $table)
        {
            $table->string('template')->nullable();
        });

        Schema::table('quotes', function (Blueprint $table)
        {
            $table->renameColumn('template', 'old_template');
        });

        Schema::table('quotes', function (Blueprint $table)
        {
            $table->string('template')->nullable();
        });

        DB::table('clients')->update(['invoice_template' => DB::raw('old_invoice_template')]);
        DB::table('clients')->update(['quote_template' => DB::raw('old_quote_template')]);
        DB::table('invoices')->update(['template' => DB::raw('old_template')]);
        DB::table('quotes')->update(['template' => DB::raw('old_template')]);

        Schema::table('clients', function (Blueprint $table)
        {
            $table->dropColumn('old_invoice_template');
        });

        Schema::table('clients', function (Blueprint $table)
        {
            $table->dropColumn('old_quote_template');
        });

        Schema::table('invoices', function (Blueprint $table)
        {
            $table->dropColumn('old_template');
        });

        Schema::table('quotes', function (Blueprint $table)
        {
            $table->dropColumn('old_template');
        });
    }
}
