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

class AddUserIdOnClients extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('clients', 'user_id'))
        {
            Schema::table('clients', function (Blueprint $table)
            {
                $table->integer('user_id')->after('updated_at');
                $table->index('user_id');
            });
        }

        $clients = DB::table('clients')->get();
        $user    = DB::table('users')->where('user_type', 'system')->first();

        foreach ($clients as $client)
        {
            $transition = DB::table('transitions')->where('action_type', 'client_created')->where('transitionable_id', $client->id)->first();

            DB::table('clients')->where('id', $client->id)->update(['user_id' => isset($transition->user_id) ? $transition->user_id : $user->id]);
        }
    }
}
