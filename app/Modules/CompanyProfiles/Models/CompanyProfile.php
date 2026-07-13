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

use FI\Modules\CustomFields\Support\CustomFieldsParser;
use FI\Modules\Expenses\Models\Expense;
use FI\Modules\Invoices\Models\Invoice;
use FI\Modules\Quotes\Models\Quote;
use Illuminate\Database\Eloquent\Model;

class CompanyProfile extends Model
{
    protected $guarded = ['id'];

    public static function getList()
    {
        return self::orderBy('company')->pluck('company', 'id')->all();
    }

    public static function inUse($id)
    {
        if (Invoice::where('company_profile_id', $id)->count())
        {
            return true;
        }

        if (Quote::where('company_profile_id', $id)->count())
        {
            return true;
        }

        if (Expense::where('company_profile_id', $id)->count())
        {
            return true;
        }

        if (config('fi.defaultCompanyProfile') == $id)
        {
            return true;
        }

        return false;
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function custom()
    {
        return $this->hasOne('FI\Modules\CustomFields\Models\CompanyProfileCustom');
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    public function getFormattedAddressAttribute()
    {
        return nl2br(formatAddress($this));
    }

    public function getLogoUrlAttribute()
    {
        if ($this->logo)
        {
            return route('companyProfiles.logo', [$this->id]);
        }
    }

    public function logo($width = null, $height = null)
    {
        if ($this->logo and file_exists(company_profile_logo_path($this->logo)))
        {
            $logo = base64_encode(file_get_contents(company_profile_logo_path($this->logo)));

            $style = '';

            if ($width and !$height)
            {
                $style = 'width: ' . $width . 'px;';
            }
            elseif ($width and $height)
            {
                $style = 'width: ' . $width . 'px; height: ' . $height . 'px;';
            }

            return '<img id="cp-logo" src="data:image/png;base64,' . $logo . '" style="' . $style . '">';
        }

        return null;
    }

    /*
    |--------------------------------------------------------------------------
    | Other
    |--------------------------------------------------------------------------
    */

    public function customField($label, $rawHtml = true)
    {
        $customField = config('fi.customFields')->where('tbl_name', 'company_profiles')->where('field_label', $label)->first();

        if ($customField)
        {
            return CustomFieldsParser::getFieldValue($this->custom, $customField, $rawHtml);
        }

        return null;

    }
}
