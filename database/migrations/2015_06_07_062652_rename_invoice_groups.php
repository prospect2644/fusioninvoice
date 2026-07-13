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
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameInvoiceGroups extends Migration
{
    public function up()
    {
        // Rename the invoice groups table to just groups
        Schema::rename('invoice_groups', 'groups');

        Schema::table('invoices', function (Blueprint $table)
        {
            $table->renameColumn('invoice_group_id', 'group_id');
        });

        Schema::table('quotes', function (Blueprint $table)
        {
            $table->renameColumn('invoice_group_id', 'group_id');
        });
    }
}
