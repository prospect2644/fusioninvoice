<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Modules\CustomFields\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CustomFieldStoreRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function attributes()
    {
        return [
            'tbl_name'    => trans('fi.table_name'),
            'field_label' => trans('fi.field_label'),
            'field_type'  => trans('fi.field_type'),
        ];
    }

    public function rules()
    {
        return [
            'tbl_name'    => 'required',
            'field_label' => ['required', 'regex:/^[a-zA-Z]+[a-zA-Z0-9-_ -]*[a-zA-Z0-9_ -]$/u',
                              Rule::unique('custom_fields')->where(function ($query)
                              {
                                  return $query->whereTblName($this->tbl_name);
                              }),
            ],
            'field_type'  => 'required',
        ];
    }

}