<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Modules\Users\Requests;

use Illuminate\Validation\Rule;

class UserUpdateRequest extends UserStoreRequest
{
    public function rules()
    {
        return [
            'email' => ['required', 'email', Rule::unique('users')->where(function ($query)
            {
                return $query->whereNotIn('id', [$this->route('id')])->whereStatus(1);
            })],
            'name'  => 'required',
        ];
    }
}