<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Modules\Settings\Requests;

use FI\Modules\Settings\Rules\ValidFile;
use Illuminate\Foundation\Http\FormRequest;

class TestEmailRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $rules = [
            'from'    => 'required',
            'to'      => 'required',
            'to.*'    => 'required|email',
            'subject' => 'required',
            'body'    => 'required',
        ];

        return $rules;
    }

    public function messages()
    {
        return [
            'to.*.required' => trans('fi.test-email-required'),
        ];
    }
}