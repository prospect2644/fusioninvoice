<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Modules\Settings\Models;

use FI\Modules\CompanyProfiles\Models\CompanyProfile;

class SettingObserver
{
    public function saving(Setting $setting)
    {
        if ($setting->setting_key == 'invoiceTemplate' or $setting->setting_key == 'quoteTemplate')
        {
            $original = $setting->getOriginal();

            if (isset($original['setting_value']) and $original['setting_value'] !== $setting->setting_value)
            {
                $templateType     = $setting->setting_key;
                $originalTemplate = $original['setting_value'];
                $newTemplate      = $setting->setting_value;

                if ($templateType == 'invoiceTemplate')
                {
                    CompanyProfile::whereNull('invoice_template')->orWhere('invoice_template', $originalTemplate)->orWhere('invoice_template', '')->update(['invoice_template' => $newTemplate]);
                }
                elseif ($templateType == 'quoteTemplate')
                {
                    CompanyProfile::whereNull('quote_template')->orWhere('quote_template', $originalTemplate)->orWhere('quote_template', '')->update(['quote_template' => $newTemplate]);
                }
            }
        }
    }
}