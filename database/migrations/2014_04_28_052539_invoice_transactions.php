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

class InvoiceTransactions extends Migration
{
    public function up()
    {
        Schema::create('invoice_transactions', function (Blueprint $table)
        {
            $table->increments('id');
            $table->timestamps();
            $table->integer('invoice_id');
            $table->boolean('is_successful');
            $table->string('transaction_reference')->nullable();
        });
    }
}
