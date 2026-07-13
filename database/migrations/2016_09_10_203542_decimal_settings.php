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

class DecimalSettings extends Migration
{
    public function up()
    {
        Setting::saveByKey('amountDecimals', 2);
        Setting::saveByKey('roundTaxDecimals', 3);
    }
}
