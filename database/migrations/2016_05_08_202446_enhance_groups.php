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

class EnhanceGroups extends Migration
{
    public function up()
    {
        Schema::table('groups', function (Blueprint $table)
        {
            $table->string('format');
            $table->integer('reset_number');
        });

        $groups = DB::table('groups')->get();

        foreach ($groups as $group)
        {
            $format = '';

            if ($group->prefix) $format .= $group->prefix;
            if ($group->prefix_year) $format .= '{YEAR}';
            if ($group->prefix_month) $format .= '{MONTH}';

            $format .= '{NUMBER}';

            DB::table('groups')
              ->where('id', $group->id)
              ->update(['format' => $format]);
        }

        Schema::table('groups', function (Blueprint $table)
        {
            $table->dropColumn(['prefix', 'prefix_year', 'prefix_month']);
        });
    }
}
