<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Modules\TaskList\Requests;

use Illuminate\Validation\Rule;

class TaskUpdateRequest extends TaskStoreRequest
{

    public function prepareForValidation()
    {
        $request = $this->all();

        if (request('id'))
        {
            $request['id'] = request('id');
        }

        $this->replace($request);
    }

    public function rules()
    {
        $user_id = auth()->user()->id;

        return [
            'id' => 'required', Rule::exists('tasks')->where(function ($query) use ($user_id)
            {
                $query->orWhere('user_id', $user_id)->orWhere('assignee_id', $user_id);
            }),
        ];
    }
}