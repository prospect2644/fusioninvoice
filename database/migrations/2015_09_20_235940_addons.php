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

class Addons extends Migration
{
    public function up()
    {
        Schema::create('addons', function (Blueprint $table)
        {
            $table->increments('id');
            $table->timestamps();
            $table->string('name');
            $table->string('author_name');
            $table->string('author_url');
            $table->longText('navigation_menu')->nullable();
            $table->longText('system_menu')->nullable();
            $table->string('path');
            $table->boolean('enabled')->default(0);
        });
    }
}
