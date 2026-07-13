<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Modules\Clients\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ContactRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function attributes()
    {
        return [
            'title'           => trans('fi.title'),
            'name'            => trans('fi.name'),
            'email'           => trans('fi.email'),
            'primary_phone'   => trans('fi.primary_phone'),
            'alternate_phone' => trans('fi.alternate_phone'),
        ];
    }

    public function rules()
    {
        return [
            'name'          => 'required',
            'email'         => 'required|email'
        ];
    }
}
