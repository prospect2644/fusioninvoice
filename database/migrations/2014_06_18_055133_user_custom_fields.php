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

class UserCustomFields extends Migration
{
    public function up()
    {
        Schema::create('users_custom', function (Blueprint $table)
        {
            $table->integer('user_id');
            $table->timestamps();

            $table->primary('user_id');
        });
    }
}
