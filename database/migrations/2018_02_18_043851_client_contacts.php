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

class ClientContacts extends Migration
{
    public function up()
    {
        Schema::create('contacts', function (Blueprint $table)
        {
            $table->increments('id');
            $table->timestamps();
            $table->integer('client_id');
            $table->string('name');
            $table->string('email');
            $table->boolean('default_to');
            $table->boolean('default_cc');
            $table->boolean('default_bcc');

            $table->index('client_id');
        });
    }
}
