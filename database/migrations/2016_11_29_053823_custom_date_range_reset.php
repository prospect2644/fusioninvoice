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

class CustomDateRangeReset extends Migration
{
    public function up()
    {
        if (Setting::getByKey('widgetInvoiceSummaryDashboardTotals') == 'custom_date_range')
        {
            Setting::saveByKey('widgetInvoiceSummaryDashboardTotals', 'year_to_date');
            Setting::deleteByKey('widgetInvoiceSummaryDashboardTotalsFromDate');
            Setting::deleteByKey('widgetInvoiceSummaryDashboardTotalsToDate');
        }

        if (Setting::getByKey('widgetQuoteSummaryDashboardTotals') == 'custom_date_range')
        {
            Setting::saveByKey('widgetQuoteSummaryDashboardTotals', 'year_to_date');
            Setting::deleteByKey('widgetQuoteSummaryDashboardTotalsFromDate');
            Setting::deleteByKey('widgetQuoteSummaryDashboardTotalsToDate');
        }
    }
}
