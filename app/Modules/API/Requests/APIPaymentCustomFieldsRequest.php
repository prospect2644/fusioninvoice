<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Modules\API\Requests;

use FI\Traits\ApiCustomFieldValidator;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class APIPaymentCustomFieldsRequest extends FormRequest
{
    use ApiCustomFieldValidator;

    private $customFieldType = 'payments';

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'payment_id' => 'required|exists:payments,id',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json(['errors' => (new ValidationException($validator))->errors()], JsonResponse::HTTP_UNPROCESSABLE_ENTITY));
    }
}