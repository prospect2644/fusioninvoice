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

class DashboardSettings extends Migration
{
    public function up()
    {
        Setting::saveByKey('widgetEnabledInvoiceSummary', 1);
        Setting::saveByKey('widgetInvoiceSummaryDashboardTotals', 'year_to_date');
        Setting::saveByKey('widgetEnabledQuoteSummary', 1);
        Setting::saveByKey('widgetQuoteSummaryDashboardTotals', 'year_to_date');
        Setting::saveByKey('widgetDisplayOrderInvoiceSummary', 1);
        Setting::saveByKey('widgetColumnWidthInvoiceSummary', 6);
        Setting::saveByKey('widgetDisplayOrderQuoteSummary', 2);
        Setting::saveByKey('widgetColumnWidthQuoteSummary', 6);
    }
}
