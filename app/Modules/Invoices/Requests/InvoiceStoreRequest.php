<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Modules\Invoices\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InvoiceStoreRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function attributes()
    {
        return [
            'company_profile_id'        => trans('fi.company_profile'),
            'client_name'               => trans('fi.client'),
            'client_id'                 => trans('fi.client'),
            'user_id'                   => trans('fi.user'),
            'summary'                   => trans('fi.summary'),
            'invoice_date'              => trans('fi.date'),
            'due_at'                    => trans('fi.due'),
            'number'                    => trans('fi.invoice_number'),
            'status'                    => trans('fi.status'),
            'exchange_rate'             => trans('fi.exchange_rate'),
            'template'                  => trans('fi.template'),
            'document_number_scheme_id' => trans('fi.document_number_scheme'),
            'items.*.name'              => trans('fi.name'),
            'items.*.quantity'          => trans('fi.quantity'),
            'items.*.price'             => trans('fi.price'),
        ];
    }

    public function rules()
    {
        return [
            'type'               => 'required',
            'company_profile_id' => 'required|integer|exists:company_profiles,id',
            'client_name'        => 'required',
            'invoice_date'       => 'required',
            'user_id'            => 'required',
        ];
    }
}