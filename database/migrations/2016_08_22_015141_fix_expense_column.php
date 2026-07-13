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

class FixExpenseColumn extends Migration
{
    public function up()
    {
        Schema::table('expenses', function (Blueprint $table)
        {
            $table->decimal('amount', 15, 2)->change();
        });
    }
}
