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

class RecentClientActivityWidget extends Migration
{
    public function up()
    {
        $maxDisplayOrder = Setting::where('setting_key', 'like', 'widgetDisplayOrder%')->max('setting_value');

        Setting::saveByKey('widgetEnabledClientActivity', 0);
        Setting::saveByKey('widgetDisplayOrderClientActivity', ($maxDisplayOrder + 1));
        Setting::saveByKey('widgetColumnWidthClientActivity', 4);
    }
}
