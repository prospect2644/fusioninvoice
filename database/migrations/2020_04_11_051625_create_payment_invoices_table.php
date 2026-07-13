<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePaymentInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_invoices', function (Blueprint $table)
        {
            $table->increments('id');
            $table->timestamps();
            $table->integer('payment_id');
            $table->integer('invoice_id');
            $table->decimal('invoice_amount_paid', 10, 2)->default(0.00);
            $table->index('invoice_id');
            $table->index('payment_id');
            $table->index('invoice_amount_paid');
        });
    }
}