<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Composers;

use FI\Modules\CompanyProfiles\Models\CompanyProfile;

class ReportComposer
{
    public function compose($view)
    {
        $view->with('companyProfiles', ['' => trans('fi.all_company_profiles')] + CompanyProfile::getList());
    }
}