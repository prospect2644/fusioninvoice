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

class AlterUsersForUserTypesDefault extends Migration
{
    /**
     * Run the migrations.
     * @return void
     */
    public function up()
    {
        DB::table('users')->whereNull('user_type')->update(['user_type' => 'admin']);
        DB::table('users')->where('user_type', '')->update(['user_type' => 'admin']);
    }

}
