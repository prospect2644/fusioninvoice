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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ActivitiesUpdate extends Migration
{
    public function up()
    {
        Schema::table('activities', function (Blueprint $table)
        {
            $table->renameColumn('object', 'audit_type');
        });

        Schema::table('activities', function (Blueprint $table)
        {
            $table->renameColumn('parent_id', 'audit_id');
        });

        DB::table('activities')->where('audit_type', 'quote')->update(['audit_type' => 'FI\Modules\Quotes\Models\Quote']);
        DB::table('activities')->where('audit_type', 'invoice')->update(['audit_type' => 'FI\Modules\Invoices\Models\Invoice']);
    }
}
