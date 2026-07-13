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
use Illuminate\Support\Facades\Schema;

class IncreaseSummaryField extends Migration
{
    public function up()
    {
        Schema::table('quotes', function ($table)
        {
            $table->string('summary', 255)->change();
        });

        Schema::table('invoices', function ($table)
        {
            $table->string('summary', 255)->change();
        });

        Schema::table('recurring_invoices', function ($table)
        {
            $table->string('summary', 255)->change();
        });
    }
}
