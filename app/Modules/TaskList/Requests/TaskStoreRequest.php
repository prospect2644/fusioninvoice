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

use Illuminate\Foundation\Http\FormRequest;

class TaskStoreRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'title'              => 'required',
            'due_date_timestamp' => 'nullable',
            'assignee_id'        => 'required|numeric|exists:users,id',
            'client_id'          => 'nullable|numeric|exists:clients,id',
            'task_section_id'    => 'required|numeric|exists:task_section,id',
        ];
    }

    public function messages()
    {
        return [
            'due_date.date'            => trans('fi.due_date_validation_date'),
            'assignee_id.required'     => trans('fi.assignee_validation_required'),
            'assignee_id.numeric'      => trans('fi.assignee_validation_numeric'),
            'assignee_id.exists'       => trans('fi.assignee_validation_exists'),
            'client_id.numeric'        => trans('fi.client_validation_numeric'),
            'client_id.exists'         => trans('fi.client_validation_exists'),
            'id.exists'                => trans('fi.task_not_authorized'),
            'task_section_id.required' => trans('fi.task_section_invalid'),
            'task_section_id.numeric'  => trans('fi.task_section_invalid'),
            'task_section_id.exists'   => trans('fi.task_section_invalid'),
        ];
    }
}