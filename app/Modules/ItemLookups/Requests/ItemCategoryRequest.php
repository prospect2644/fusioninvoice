<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Modules\ItemLookups\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ItemCategoryRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function attributes()
    {
        return [
            'name' => trans('fi.category'),
        ];
    }

    public function rules()
    {
        if ($this->route('id'))
        {
            return [
                'name' => 'required|max:255|unique:item_categories,name,' . $this->route('id'),
            ];
        }
        else
        {
            return [
                'name' => 'required|max:255|unique:item_categories,name',
            ];
        }
    }
}