<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use FI\Modules\Payments\Models\PaymentInvoice;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class MigrateDataPaymentInvoicesToPayments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $paymentInvoices = PaymentInvoice::with('invoice')->get();

        if ($paymentInvoices->count() > 0)
        {
            foreach ($paymentInvoices as $paymentInvoice)
            {
                DB::table('payments')->where('id', $paymentInvoice->payment_id)->update(['client_id' => $paymentInvoice->invoice->client->id]);
            }
        }
    }

}
