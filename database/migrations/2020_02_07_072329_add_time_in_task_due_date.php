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
use Illuminate\Support\Facades\DB;

class AddTimeInTaskDueDate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Setting::saveByKey('includeTimeInTaskDueDate', 0);

        $users = DB::table('users')->whereIn('user_type', ['admin', 'standard_user'])->get();

        foreach ($users as $user)
        {
            DB::table('user_settings')->insert([
                'created_at'    => $user->created_at,
                'updated_at'    => $user->updated_at,
                'user_id'       => $user->id,
                'setting_key'   => 'includeTimeInTaskDueDate',
                'setting_value' => 0,
            ]);
        }
    }
}
