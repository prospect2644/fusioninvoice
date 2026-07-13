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

class AddUserIdOnPayments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('payments', 'user_id'))
        {
            Schema::table('payments', function (Blueprint $table)
            {
                $table->integer('user_id')->after('updated_at');
                $table->index('user_id');
            });
        }

        $payments = Payment::with('paymentInvoice.invoice')->get();
        $user     = DB::table('users')->where('user_type', 'system')->first();

        foreach ($payments as $payment)
        {
            $userId = isset($payment->paymentInvoice[0]->invoice->user_id) ? $payment->paymentInvoice[0]->invoice->user_id : $user->id;

            DB::table('payments')->where('id', $payment->id)->update(['user_id' => $userId]);
        }
    }
}
