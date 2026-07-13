<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Modules\DocumentNumberSchemes\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DocumentNumberSchemeRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function attributes()
    {
        return [
            'name'     => trans('fi.name'),
            'type'     => trans('fi.type'),
            'next_id'  => trans('fi.next_number'),
            'left_pad' => trans('fi.left_pad'),
            'format'   => trans('fi.format'),
        ];
    }

    public function rules()
    {
        return [
            'name'     => 'required',
            'type'     => 'required',
            'next_id'  => 'required|integer',
            'left_pad' => 'required|numeric',
            'format'   => 'required',
        ];
    }
}