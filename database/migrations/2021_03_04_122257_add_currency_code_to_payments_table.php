<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use FI\Modules\Settings\Models\Setting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCurrencyCodeToPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('currency_code')->after('amount')->nullable();
        });

        DB::table('payments')->join('invoices', function ($join) {
            $join->on('payments.credit_memo_id', '=', 'invoices.id')
                 ->whereNotNull('payments.credit_memo_id');
        })->whereNotNull('payments.credit_memo_id')
          ->update(['payments.currency_code' => DB::raw('invoices.currency_code')]);

        $appliedPayments = DB::table('payments')
                             ->select(DB::raw('DISTINCT payments.id'), 'invoices.currency_code')
                             ->leftJoin('payment_invoices', 'payments.id', '=', 'payment_invoices.payment_id')
                             ->join('invoices', 'payment_invoices.invoice_id', '=', 'invoices.id')
                             ->whereNull('payments.credit_memo_id')
                             ->get();
        foreach($appliedPayments as $appliedPayment){
            DB::table('payments')
              ->where('id', $appliedPayment->id)
              ->update(['currency_code' => $appliedPayment->currency_code]);
        }

        $prePayments = DB::table('payments')
                         ->whereNull('payments.credit_memo_id')
                         ->whereNotExists(function ($query) {
                             $query->select(DB::raw(1))
                                   ->from('payment_invoices')
                                   ->whereColumn('payment_invoices.payment_id', 'payments.id');
                         })->get();

        foreach($prePayments as $prePayment){
            DB::table('payments')
              ->where('id', $prePayment->id)
              ->update(['currency_code' => Setting::getByKey('baseCurrency')]);
        }
    }
}
