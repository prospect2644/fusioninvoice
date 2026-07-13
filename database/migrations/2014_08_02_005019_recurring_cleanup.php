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

class RecurringCleanup extends Migration
{
    public function up()
    {
        // Previously, recurring invoices were not being deleted when the base
        // invoice was deleted. This was causing recurring invoices not to
        // generate properly. Deleting a base invoice now deletes the recurring
        // invoice, but we need to clean up any orphaned records still in the
        // system from before the bug was fixed.

        DB::table('recurring_invoices')->whereNotIn('invoice_id', function ($query)
        {
            $query->select('id')->from('invoices');
        })->delete();
    }
}
