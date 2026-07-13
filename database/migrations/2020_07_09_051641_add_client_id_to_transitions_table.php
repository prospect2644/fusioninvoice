<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddClientIdToTransitionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('transitions', function (Blueprint $table)
        {

            $table->renameColumn('transitionable_field', 'action_type');
            $table->renameColumn('previous_state', 'previous_value');
            $table->renameColumn('state', 'current_value');
            $table->integer('client_id')->nullable()->after('user_id');
            /*
             * TODO: Need to replace with json data type when Laravel, PHP and MySql version upgrade.
             */
            $table->text('detail')->nullable();
        });
    }
}
