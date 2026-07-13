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

class Viewed extends Migration
{
    public function up()
    {
        Schema::table('invoices', function (Blueprint $table)
        {
            $table->boolean('viewed')->default(0);
        });

        Schema::table('quotes', function (Blueprint $table)
        {
            $table->boolean('viewed')->default(0);
        });

        DB::table('invoices')->whereIn('id', function ($query)
        {
            $query->select('audit_id')->from('activities')->where('audit_type', 'FI\Modules\Invoices\Models\Invoice');
        })->update(['viewed' => 1]);

        DB::table('quotes')->whereIn('id', function ($query)
        {
            $query->select('audit_id')->from('activities')->where('audit_type', 'FI\Modules\Quotes\Models\Quote');
        })->update(['viewed' => 1]);
    }
}