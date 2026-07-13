<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Modules\Dashboard\Controllers;

use Carbon\Carbon;
use Cookie;
use FI\Http\Controllers\Controller;
use FI\Modules\Settings\Models\UserSetting;
use FI\Support\DashboardWidgets;

class DashboardController extends Controller
{
    public function index()
    {
        return view('dashboard.index')
            ->with('widgets', DashboardWidgets::listsByOrder())
            ->with('dashboardWidgetsDateOptions', periods());
    }

    public function updateWidgetSettings()
    {
        UserSetting::saveByKey('dashboardWidgetsDateOption', request('dashboardWidgetsDateOption'), auth()->user());
        if (request('dashboardWidgetsDateOption') == 'custom_date_range')
        {
            if (request('dashboardWidgetsFromDate'))
            {
                $fromDate = Carbon::createFromFormat(config('fi.dateFormat'), request('dashboardWidgetsFromDate'))->format('Y-m-d');
                UserSetting::saveByKey('dashboardWidgetsFromDate', $fromDate, auth()->user());
            }
            if (request('dashboardWidgetsToDate'))
            {
                $toDate = Carbon::createFromFormat(config('fi.dateFormat'), request('dashboardWidgetsToDate'))->format('Y-m-d');
                UserSetting::saveByKey('dashboardWidgetsToDate', $toDate, auth()->user());
            }
        }
        else
        {
            UserSetting::saveByKey('dashboardWidgetsFromDate', '', auth()->user());
            UserSetting::saveByKey('dashboardWidgetsToDate', '', auth()->user());
        }
    }

    public function versionCheckPreference()
    {
        Cookie::queue(Cookie::forever('versionCheck', 0));
        Cookie::queue(Cookie::forever('versionCheckDate', Carbon::now()));
        session(['versionAlert' => '']);
    }

    public function agreementCheckPreference()
    {
        Cookie::queue(Cookie::forever('agreementCheck', 0));
        Cookie::queue(Cookie::forever('agreementCheckDate', Carbon::now()));
        session(['agreementExpireAlert' => '']);
        session(['agreementExpiredAlert' => '']);
    }
}