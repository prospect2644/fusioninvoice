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

class MerchantTables extends Migration
{
    public function up()
    {
        Schema::create('merchant_clients', function (Blueprint $table)
        {
            $table->increments('id');
            $table->timestamps();
            $table->string('driver');
            $table->integer('client_id');
            $table->string('merchant_key');
            $table->string('merchant_value');

            $table->index('driver');
            $table->index('client_id');
            $table->index('merchant_key');
        });

        Schema::create('merchant_payments', function (Blueprint $table)
        {
            $table->increments('id');
            $table->timestamps();
            $table->string('driver');
            $table->integer('payment_id');
            $table->string('merchant_key');
            $table->string('merchant_value');

            $table->index('driver');
            $table->index('payment_id');
            $table->index('merchant_key');
        });
    }
}
