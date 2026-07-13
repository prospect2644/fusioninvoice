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

class ClientType extends Migration
{
    public function up()
    {
        Schema::table('clients', function (Blueprint $table)
        {
            $table->enum('type', ['lead', 'prospect', 'customer'])->nullable();
        });

        DB::table('clients')->whereIn('id', function ($query)
        {
            $query->select('client_id')->from('invoices')->distinct()->get();
        })->update(['type' => 'customer']);

        DB::table('clients')->whereIn('id', function ($query)
        {
            $query->select('client_id')->from('recurring_invoices')->distinct()->get();
        })->update(['type' => 'customer']);

        DB::table('clients')->whereNull('type')->update(['type' => 'lead']);
    }
}
