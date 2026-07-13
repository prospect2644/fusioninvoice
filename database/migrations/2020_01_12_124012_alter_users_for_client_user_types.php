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

class AlterUsersForClientUserTypes extends Migration
{
    /**
     * Run the migrations.
     * @return void
     */
    public function up()
    {
        DB::table('users')->where('client_id', '>', 0)->update(['user_type' => 'client']);
    }

}
