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
use FI\Modules\Invoices\Models\Invoice;
use FI\Support\NumberFormatter;
use FI\Traits\CustomFieldValidator;
use Illuminate\Foundation\Http\FormRequest;

class PaymentRequest extends FormRequest
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
            'client_id'         => trans('fi.client'),
            'paid_at'           => trans('fi.payment_date'),
            'invoice_id'        => trans('fi.invoice'),
            'amount'            => trans('fi.amount'),
            'remaining_balance' => trans('fi.remaining_balance'),
            'payment_method_id' => trans('fi.payment_method'),
        ];
    }

    public function prepareForValidation()
    {
        $request  = $this->all();
        $currency = Currency::getByCode($request['currency_code']);

        $request['amount']            = (isset($request['amount'])) ? NumberFormatter::unformat($request['amount'], $currency) : null;
        $request['remaining_balance'] = (isset($request['remaining_balance'])) ? NumberFormatter::unformat($request['remaining_balance'], $currency) : null;

        $this->replace($request);
    }


    public function rules()
    {
        return [
            'client_id'         => 'required|exists:clients,id',
            'paid_at'           => 'required',
            'invoice_id'        => 'required|exists:invoices,id',
            'amount'            => [
                'required',
                'numeric',
                function ($attribute, $value, $fail)
                {
                    $invoice = Invoice::find($this->input('invoice_id'));
                    if ($value > $invoice->amount->balance)
                    {
                        $fail(trans('fi.entered_amount_less_than_invoice_amount'));
                    }
                },
            ],
            'remaining_balance' => 'required|numeric',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'currency_code'     => 'required|exists:currencies,code',
        ];
    }
}
