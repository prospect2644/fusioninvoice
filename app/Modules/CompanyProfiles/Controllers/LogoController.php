<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Modules\CompanyProfiles\Controllers;

use FI\Http\Controllers\Controller;
use FI\Modules\CompanyProfiles\Models\CompanyProfile;

class LogoController extends Controller
{
    public function logo($id)
    {
        $companyProfile = CompanyProfile::find($id);

        if ($companyProfile->logo)
        {
            return response(file_get_contents(company_profile_logo_path($companyProfile->logo)), 200)->header('Content-Type', 'image/jpeg');
        }

        return null;
    }
}
