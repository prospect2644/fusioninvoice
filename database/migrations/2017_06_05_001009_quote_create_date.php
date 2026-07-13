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

class QuoteCreateDate extends Migration
{
    public function up()
    {
        Schema::table('quotes', function (Blueprint $table)
        {
            $table->date('quote_date')->after('updated_at');
        });

        DB::table('quotes')->update(['quote_date' => DB::raw('created_at')]);
    }
}
