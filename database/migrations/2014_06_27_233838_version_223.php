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

class Version223 extends Migration
{
    public function up()
    {
        // Delete invalid quote activity records if they exist
        DB::table('activities')->where('audit_type', 'FI\Modules\Quotes\Models\Quote')->whereNotIn('audit_id', function ($query)
        {
            $query->select('id')->from('quotes');
        })->delete();
        // Delete invalid invoice activity records if they exist
        DB::table('activities')->where('audit_type', 'FI\Modules\Invoices\Models\Invoice')->whereNotIn('audit_id', function ($query)
        {
            $query->select('id')->from('invoices');
        })->delete();

        DB::table('settings')->where('setting_key', 'version')->update(['setting_value' => '2.2.3']);
    }
}
