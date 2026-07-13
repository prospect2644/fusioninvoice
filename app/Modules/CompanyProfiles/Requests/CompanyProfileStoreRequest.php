<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Modules\CompanyProfiles\Requests;

use FI\Traits\CustomFieldValidator;
use Illuminate\Foundation\Http\FormRequest;

class CompanyProfileStoreRequest extends FormRequest
{
    use CustomFieldValidator;

    private $customFieldType = 'company_profiles';

    public function authorize()
    {
        return true;
    }

    public function attributes()
    {
        return ['company' => trans('fi.company')];
    }

    public function rules()
    {
        return [
            'company' => 'required|unique:company_profiles,company',
            'logo'    => 'nullable|image|mimes:jpeg,png,jpg,gif',
        ];
    }
}