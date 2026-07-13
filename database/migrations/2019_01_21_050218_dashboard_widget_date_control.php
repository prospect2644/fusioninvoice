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

class DashboardWidgetDateControl extends Migration
{
    public function up()
    {
        Setting::saveByKey('dashboardWidgetsDateOption', Setting::getByKey('widgetInvoiceSummaryDashboardTotals'));
        Setting::saveByKey('dashboardWidgetsFromDate', Setting::getByKey('widgetInvoiceSummaryDashboardTotalsFromDate') ?: '');
        Setting::saveByKey('dashboardWidgetsToDate', Setting::getByKey('widgetInvoiceSummaryDashboardTotalsToDate') ?: '');

        Setting::deleteByKey('widgetInvoiceSummaryDashboardTotals');
        Setting::deleteByKey('widgetInvoiceSummaryDashboardTotalsFromDate');
        Setting::deleteByKey('widgetInvoiceSummaryDashboardTotalsToDate');
        Setting::deleteByKey('widgetQuoteSummaryDashboardTotals');
        Setting::deleteByKey('widgetQuoteSummaryDashboardTotalsFromDate');
        Setting::deleteByKey('widgetQuoteSummaryDashboardTotalsToDate');
    }
}
