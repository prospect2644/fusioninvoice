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

class GroupsLastGenerated extends Migration
{
    public function up()
    {
        Schema::table('groups', function (Blueprint $table)
        {
            $table->integer('last_id');
            $table->integer('last_year');
            $table->integer('last_month');
            $table->integer('last_week');
        });
    }
}
