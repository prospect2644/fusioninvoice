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

class ClientUserAccounts extends Migration
{
    public function up()
    {
        DB::table('users')->join('clients', 'users.client_id', '=', 'clients.id')
            ->where('clients.allow_login', 0)
            ->delete();

        DB::table('users')->where('client_id', '<>', 0)->whereNotIn('users.client_id', function ($query)
        {
            $query->select('id')->from('clients');
        })->delete();
    }
}
