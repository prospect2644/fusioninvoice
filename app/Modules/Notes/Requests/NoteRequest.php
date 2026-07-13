<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Modules\Notes\Requests;

use Illuminate\Foundation\Http\FormRequest;

class NoteRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $model = base64_decode(request('model'));

        return [
            'note'               => 'required',
            'due_date_timestamp' => 'nullable',
            'tags'               => $model == 'FI\Modules\Clients\Models\Client' && config('fi.requireTagsOnClientNotes') == 1 ? 'required' : 'sometimes',
        ];
    }
}