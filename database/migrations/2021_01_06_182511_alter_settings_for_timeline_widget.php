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

class AlterSettingsForTimelineWidget extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void 
     */
    public function up()
    {
        Setting::saveByKey('widgetEnabledClientTimeLine', 1);
        Setting::saveByKey('widgetDisplayOrderClientTimeLine', 3);
        Setting::saveByKey('widgetColumnWidthClientTimeLine', 12);
    }
}
