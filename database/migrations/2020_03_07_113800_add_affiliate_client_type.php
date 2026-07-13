<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use FI\Modules\Clients\Models\Client;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAffiliateClientType extends Migration
{
    public function up()
    {
        DB::statement("ALTER TABLE " . DB::getTablePrefix() . "clients MODIFY type ENUM('lead', 'prospect', 'customer', 'affiliate')");
    }
}
