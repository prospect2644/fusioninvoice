<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use FI\Modules\Invoices\Models\Invoice;
use FI\Modules\Payments\Models\Payment;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class MigrateDataPaymentsToPaymentInvoices extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        try
        {
            $payments = Payment::all();

            DB::beginTransaction();

            if ($payments->count() > 0)
            {

                foreach ($payments as $payment)
                {
                    $invoiceDetail = Invoice::with('amount', 'client')->find($payment->invoice_id);

                    if (isset($invoiceDetail->id) && $invoiceDetail->amount->balance < 0)
                    {

                        DB::table('payment_invoices')->insertGetId([
                            'payment_id'          => $payment->id,
                            'invoice_id'          => $payment->invoice_id,
                            'invoice_amount_paid' => $invoiceDetail->amount->total,
                            'created_at'          => $payment->paid_at,
                        ]);

                        DB::table('payments')
                            ->where('id', $payment->id)
                            ->update(['client_id' => $invoiceDetail->client->id, 'remaining_balance' => ($invoiceDetail->amount->paid - $invoiceDetail->amount->total)]);
                    }
                    else
                    {
                        DB::table('payment_invoices')->insert([
                            'payment_id'          => $payment->id,
                            'invoice_id'          => $payment->invoice_id,
                            'invoice_amount_paid' => $payment->amount,
                            'created_at'          => $payment->paid_at,
                        ]);
                    }
                }
            }

            DB::commit();

            // Need to remove invoice_id from payment table
            Schema::table('payments', function ($table)
            {
                $table->dropColumn('invoice_id');
            });
        }
        catch (\PDOException $e)
        {
            DB::rollBack();
        }
    }
}