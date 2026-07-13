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

class ClientsEmailPaymentReceipt extends Migration
{
    public function up()
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->enum('automatic_email_payment_receipt', ['default', 'yes', 'no'])->default('default');
        });
    }
}
