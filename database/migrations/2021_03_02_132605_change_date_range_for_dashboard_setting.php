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

class ChangeDateRangeForDashboardSetting extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('user_settings')->where('setting_key', 'dashboardWidgetsDateOption')->update(['setting_value' => 'this_month']);
        DB::table('user_settings')->where('setting_key', 'dashboardWidgetsFromDate')->update(['setting_value' => '']);
        DB::table('user_settings')->where('setting_key', 'dashboardWidgetsToDate')->update(['setting_value' => '']);

        DB::table('settings')->where('setting_key', 'dashboardWidgetsDateOption')->update(['setting_value' => 'this_month']);
        DB::table('settings')->where('setting_key', 'dashboardWidgetsFromDate')->update(['setting_value' => '']);
        DB::table('settings')->where('setting_key', 'dashboardWidgetsToDate')->update(['setting_value' => '']);

    }
}
