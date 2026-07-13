<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ExchangeRateLengthIssue extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE " . DB::getTablePrefix() . "invoices CHANGE exchange_rate exchange_rate DECIMAL(12,7) NOT NULL DEFAULT 1.0000000");
        DB::statement("ALTER TABLE " . DB::getTablePrefix() . "quotes CHANGE exchange_rate exchange_rate DECIMAL(12,7) NOT NULL DEFAULT 1.0000000");

        Schema::table('recurring_invoices', function (Blueprint $table)
        {
            $table->decimal('exchange_rate', 12, 7)->change();
        });
    }

}
