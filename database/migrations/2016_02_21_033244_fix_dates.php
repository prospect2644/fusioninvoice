<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Carbon\Carbon;
use FI\Modules\Invoices\Models\Invoice;
use FI\Modules\Settings\Models\Setting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class FixDates extends Migration
{
    public function up()
    {
        $invoicesDueAfter = Setting::getByKey('invoicesDueAfter');

        if (is_numeric($invoicesDueAfter))
        {
            $invoices = Invoice::where('due_at', '0000-00-00')->get();

            foreach ($invoices as $invoice)
            {
                DB::table('invoices')->where('id', $invoice->id)->update(['due_at' => Carbon::createFromFormat('Y-m-d H:i:s', $invoice->created_at)->addDays($invoicesDueAfter)]);
            }
        }

        DB::table('payments')->where('paid_at', '0000-00-00')->update(['paid_at' => DB::raw('created_at')]);
    }
}
