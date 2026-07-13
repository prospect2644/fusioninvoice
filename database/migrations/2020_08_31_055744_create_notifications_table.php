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

class CreateNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notifications', function (Blueprint $table)
        {
            $table->increments('id');
            $table->nullableMorphs('notifiable');
            $table->integer('user_id');
            $table->text('detail')->comment('JSON type - supporting data')->nullable();
            $table->string('action_type');
            $table->boolean('is_viewed')->default(0);
            $table->datetime('viewed_at')->nullable();
            $table->timestamps();
        });
    }
}
