<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use FI\Modules\Payments\Models\Payment;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddPaymentTypeOnPayments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payments', function (Blueprint $table)
        {
            $table->enum('type', ['single', 'pre-payment', 'credit-memo'])->after('client_id')->default('single');
        });

        $payments = Payment::all();

        DB::beginTransaction();

        if ($payments->count() > 0)
        {
            foreach ($payments as $payment)
            {
                if ($payment->credit_memo_id != null)
                {
                    $payment->type = 'credit-memo';
                    $payment->save();
                }

            }
        }

        DB::commit();
    }
}
