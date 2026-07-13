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

use Illuminate\Validation\Rule;

class CustomFieldUpdateRequest extends CustomFieldStoreRequest
{
    public function rules()
    {
        return [
            'field_label' => ['required', 'regex:/^[a-zA-Z]+[a-zA-Z0-9-_ -]*[a-zA-Z0-9_ -]$/u',
                              Rule::unique('custom_fields')->where(function ($query)
                              {
                                  return $query->whereTblName($this->tbl_name)->where('id', '<>', $this->id);
                              }),
            ],
            'field_type'  => 'required',
        ];
    }
}