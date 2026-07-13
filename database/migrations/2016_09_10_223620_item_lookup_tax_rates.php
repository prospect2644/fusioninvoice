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
use Illuminate\Support\Facades\Schema;

class ItemLookupTaxRates extends Migration
{
    public function up()
    {
        Schema::table('item_lookups', function (Blueprint $table)
        {
            $table->integer('tax_rate_id')->default(0);
            $table->integer('tax_rate_2_id')->default(0);

            $table->index('tax_rate_id');
            $table->index('tax_rate_2_id');
        });
    }
}
