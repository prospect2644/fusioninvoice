<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use FI\Modules\Settings\Models\Setting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddClientListIdentifier extends Migration
{
    public function up()
    {
        Schema::table('clients', function (Blueprint $table)
        {
            $table->string('unique_name')->nullable();

            $table->index('unique_name');
        });

        DB::table('clients')->update(['unique_name' => DB::raw('name')]);

        Setting::saveByKey('displayClientUniqueName', '0');
    }
}