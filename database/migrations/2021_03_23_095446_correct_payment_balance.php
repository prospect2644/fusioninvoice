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
use Illuminate\Support\Facades\DB;

class CorrectPaymentBalance extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $fixedPaymentIds = [];
        $payments        = DB::table('payments')->select(['id', 'amount', 'remaining_balance'])->where('remaining_balance', '>', 0)->orderBy('id', 'DESC')->get();
        foreach ($payments as $payment)
        {
            $invoice_amount_paid = DB::table('payment_invoices')->wherePaymentId($payment->id)->sum('invoice_amount_paid');
            if ($invoice_amount_paid == $payment->amount)
            {
                DB::table('payments')->whereId($payment->id)->update(['remaining_balance' => 0]);
                $fixedPaymentIds[] = $payment->id;
            }
        }
        if (count($fixedPaymentIds) > 0)
        {
            Log::info('Migration correct_payment_balance fixed ' . count($fixedPaymentIds) . ' payment remaining balance records.');
            Log::info('Migration correct_payment_balance payment IDs fixed: ' . implode(', ', $fixedPaymentIds));
        }
    }

}