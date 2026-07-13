<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Modules\Sessions\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SessionRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function attributes()
    {
        return [
            'email'    => trans('fi.email'),
            'password' => trans('fi.password'),
            'captcha'  => trans('fi.answer'),
        ];
    }

    public function rules()
    {
        $rules = [
            'email'    => 'required|email',
            'password' => 'required',
        ];
        if (config('fi.useCaptchInLogin'))
        {
            $rules['captcha'] = 'required|captcha';
        }
        return $rules;
    }
}