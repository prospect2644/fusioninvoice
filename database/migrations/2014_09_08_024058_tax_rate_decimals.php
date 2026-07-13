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

class TaxRateDecimals extends Migration
{
    public function up()
    {
        Schema::table('tax_rates', function (Blueprint $table)
        {
            $table->renameColumn('percent', 'old_percent');
        });

        Schema::table('tax_rates', function (Blueprint $table)
        {
            $table->decimal('percent', 5, 3)->default(0.00);
        });

        DB::table('tax_rates')->update(['percent' => DB::raw('old_percent')]);

        Schema::table('tax_rates', function (Blueprint $table)
        {
            $table->dropColumn('old_percent');
        });
    }
}
