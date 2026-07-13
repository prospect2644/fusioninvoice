<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Modules\Payments\Requests;

use FI\Modules\Currencies\Models\Currency;
use FI\Support\NumberFormatter;
use FI\Traits\CustomFieldValidator;
use Illuminate\Foundation\Http\FormRequest;

class CreatePaymentRequest extends FormRequest
{
    use CustomFieldValidator;

    private $customFieldType = 'payments';

    public function authorize()
    {
        return true;
    }

    public function attributes()
    {
        return [
            'paid_at'           => trans('fi.payment_date'),
            'client_id'         => trans('fi.client'),
            'amount'            => trans('fi.payment_amount'),
            'payment_method_id' => trans('fi.payment_method'),
        ];
    }

    public function prepareForValidation()
    {
        $request           = $this->all();
        $currency          = Currency::getByCode($request['currency_code']);
        $request['amount'] = (isset($request['amount'])) ? NumberFormatter::unformat($request['amount'], $currency) : null;

        $this->replace($request);
    }

    public function rules()
    {
        return [
            'paid_at'           => 'required',
            'client_id'         => 'required|exists:clients,id',
            'amount'            => 'required|numeric',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'currency_code'     => 'required|exists:currencies,code',
        ];
    }
}
