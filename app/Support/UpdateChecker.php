<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Support;

use FI\Modules\CompanyProfiles\Models\CompanyProfile;
use FI\Modules\Settings\Models\Setting;
use Illuminate\Support\Facades\Http;

class UpdateChecker
{
    protected $currentVersion;
    protected $agreementDate;

    public function checkVersion($request_type)
    {
        $companyProfile = CompanyProfile::find(config('fi.defaultCompanyProfile'));
        if (!$companyProfile)
        {
            $companyProfile = CompanyProfile::query()->first();
            if ($companyProfile)
            {
                Setting::saveByKey('defaultCompanyProfile', $companyProfile->id);
                request()->session()->put('alertInfo', trans('fi.default_company_profile_set'));
            }
        }
        if ($companyProfile)
        {
            $this->currentVersion = Http::get('https://www.fusioninvoice.com/current-version/' . config('app.key') . '/' . urlencode($companyProfile->company) . '/' . $request_type . '/' . config('fi.version'))->body();
        }
        else
        {
            $this->currentVersion = null;
        }
    }

    public function checkAgreementDate($request_type)
    {
        $companyProfile = CompanyProfile::find(config('fi.defaultCompanyProfile'));
        if (!$companyProfile)
        {
            $companyProfile = CompanyProfile::query()->first();
            if ($companyProfile)
            {
                Setting::saveByKey('defaultCompanyProfile', $companyProfile->id);
                request()->session()->put('alertInfo', trans('fi.default_company_profile_set'));
            }
        }
        if ($companyProfile)
        {
            $this->agreementDate = Http::get('https://www.fusioninvoice.com/agreement-date/' . config('app.key') . '/' . urlencode($companyProfile->company) . '/' . $request_type . '/' . config('fi.version'))->body();
        }
        else
        {
            $this->agreementDate = null;
        }
    }

    /**
     * Check to see if there is a newer version available for download.
     *
     * @return boolean
     */
    public function updateAvailable()
    {
        $currentVersion = str_replace('-', '', $this->currentVersion);
        if (is_numeric($currentVersion) && $currentVersion > str_replace('-', '', config('fi.version')))
        {
            return true;
        }

        return false;
    }

    /**
     * Getter for current version.
     *
     * @return string
     */
    public function getCurrentVersion()
    {
        return $this->currentVersion;
    }

    public function getAgreementExpireDate()
    {
        return $this->agreementDate;
    }
}