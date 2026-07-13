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

use FI\Modules\Invoices\Requests\InvoiceStoreRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class APIInvoiceShowRequest extends InvoiceStoreRequest
{
    public function rules()
    {
        $rules = parent::rules();

        unset($rules['user_id']);

        $rules['client_id'] = 'required|exists:clients,id';

        return $rules;
    }

    public function attributes()
    {
        $attributes = array_keys(parent::attributes());

        return array_combine($attributes, $attributes);
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json(['errors' => (new ValidationException($validator))->errors()], JsonResponse::HTTP_UNPROCESSABLE_ENTITY));
    }
}