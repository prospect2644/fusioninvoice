<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Modules\Clients\Requests;

use FI\Traits\CustomFieldValidator;
use Illuminate\Foundation\Http\FormRequest;
use FI\Modules\Invoices\Models\Invoice;
use FI\Modules\RecurringInvoices\Models\RecurringInvoice;

class ClientStoreRequest extends FormRequest
{
    use CustomFieldValidator;

    private $customFieldType = 'clients';

    public function authorize()
    {
        return true;
    }

    public function attributes()
    {
        return [
            'name'        => trans('fi.name'),
            'unique_name' => trans('fi.unique_name'),
            'email'       => trans('fi.email'),
        ];
    }

    public function prepareForValidation()
    {
        $request = $this->all();

        $request['email'] = $this->input('client_email', $this->input('email', ''));

        unset($request['client_email']);

        $this->replace($request);
    }

    public function rules()
    {
        return [
            'name'           => 'required',
            'unique_name'    => 'required_with:name|unique:clients',
            'email'          => 'required_if:allow_client_center_login,1|email',
            'invoice_prefix' => 'nullable|max:5',
            'type'           => 'required|in:lead,prospect,customer,affiliate',
        ];
    }

    // Validator that shows an error when trying to set a client with invoices to Lead or Prospect.
    public function withValidator($validator)
    {
        $validator->after(function ($validator)
        {
            if (($this->type == 'lead' or $this->type == 'prospect') and
                (Invoice::where('client_id', $this->id)->count() > 0
                    or RecurringInvoice::where('client_id', $this->id)->count() > 0)
            )
            {
                $validator->errors()->add('field', trans('fi.lead-or-prospect-with-invoices-error'));
            }
        });
    }

}