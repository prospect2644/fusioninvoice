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

class DiscountAmounts extends Migration
{
    public function up()
    {
        Schema::table('invoice_amounts', function (Blueprint $table)
        {
            $table->decimal('discount', 15, 2)->default(0.00)->after('subtotal');
        });

        Schema::table('quote_amounts', function (Blueprint $table)
        {
            $table->decimal('discount', 15, 2)->default(0.00)->after('subtotal');
        });
    }
}
