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

class AddMissingRecurringCustom extends Migration
{
    public function up()
    {
        $recurringInvoices = DB::table('recurring_invoices')->whereNotIn('id', function ($query)
        {
            $query->select('id')->from('recurring_invoices_custom');
        })->get();

        foreach ($recurringInvoices as $recurringInvoice)
        {
            DB::table('recurring_invoices_custom')
                ->insert([
                    'created_at'           => $recurringInvoice->created_at,
                    'updated_at'           => $recurringInvoice->updated_at,
                    'recurring_invoice_id' => $recurringInvoice->id,
                ]);
        }
    }
}
