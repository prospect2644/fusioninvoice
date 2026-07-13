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

class Amounts extends Migration
{
    public function up()
    {
        Schema::table('invoice_amounts', function (Blueprint $table)
        {
            $table->dropColumn('tax_total');
        });

        Schema::table('quote_amounts', function (Blueprint $table)
        {
            $table->dropColumn('tax_total');
        });

        Schema::table('invoice_amounts', function (Blueprint $table)
        {
            $table->renameColumn('item_subtotal', 'subtotal');
            $table->renameColumn('item_tax_total', 'tax');
        });

        Schema::table('quote_amounts', function (Blueprint $table)
        {
            $table->renameColumn('item_subtotal', 'subtotal');
            $table->renameColumn('item_tax_total', 'tax');
        });

        Schema::table('invoice_item_amounts', function (Blueprint $table)
        {
            $table->renameColumn('tax_total', 'tax');
        });

        Schema::table('quote_item_amounts', function (Blueprint $table)
        {
            $table->renameColumn('tax_total', 'tax');
        });
    }
}
