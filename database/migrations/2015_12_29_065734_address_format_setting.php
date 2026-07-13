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

class AddressFormatSetting extends Migration
{
    public function up()
    {
        Setting::saveByKey('addressFormat', "{{ address }}\r\n{{ city }}, {{ state }} {{ postal_code }}");
    }
}
