<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Modules\CompanyProfiles\Models;

use FI\Modules\CustomFields\Models\CompanyProfileCustom;

class CompanyProfileObserver
{
    public function created(CompanyProfile $companyProfile)
    {
        $companyProfile->custom()->save(new CompanyProfileCustom());
    }

    public function creating(CompanyProfile $companyProfile)
    {
        if (!$companyProfile->invoice_template)
        {
            $companyProfile->invoice_template = config('fi.invoiceTemplate');
        }

        if (!$companyProfile->quote_template)
        {
            $companyProfile->quote_template = config('fi.quoteTemplate');
        }
    }

    public function deleted(CompanyProfile $companyProfile)
    {
        $companyProfile->custom->delete();
    }

    public function saving(CompanyProfile $companyProfile)
    {
        $companyProfile->address = strip_tags($companyProfile->address);
    }
}