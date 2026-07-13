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

class CompanyProfilesDelCols extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table)
        {
            $table->dropColumn(['company', 'address', 'city', 'state', 'zip', 'country', 'phone', 'fax', 'mobile', 'web']);
        });
    }
}
