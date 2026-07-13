<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Modules\Settings\Requests;

use FI\Modules\Settings\Rules\ValidFile;
use Illuminate\Foundation\Http\FormRequest;

class SettingUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function attributes()
    {
        return [
            'setting.invoicesDueAfter'  => trans('fi.invoices_due_after'),
            'setting.quotesExpireAfter' => trans('fi.quotes_expire_after'),
            'setting.pdfBinaryPath'     => trans('fi.binary_path'),
        ];
    }

    public function rules()
    {
        $rules = [
            'setting.invoicesDueAfter'         => 'required|numeric',
            'setting.quotesExpireAfter'        => 'required|numeric',
            'setting.pdfBinaryPath'            => ['required_if:setting.pdfDriver,wkhtmltopdf', new ValidFile],
            'setting.dashboardWidgetsFromDate' => ['required_if:setting.dashboardWidgetsDateOptions,custom_date_range'],
            'setting.dashboardWidgetsToDate'   => ['required_if:setting.dashboardWidgetsDateOptions,custom_date_range'],
            'setting.mailFromAddress'          => 'required|email',
        ];

        if ($this->request->get('email-test'))
        {
            $rules = array_merge($rules, ['setting.testEmailAddress' => 'required|email']);
        }

        foreach (config('fi.settingValidationRules') as $settingValidationRules)
        {
            $rules = array_merge($rules, $settingValidationRules['rules']);
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'test_email_address.required'                  => trans('fi.test-email-required'),
            'setting.mailFromAddress.required'             => trans('fi.mail-from-required'),
            'setting.mailFromAddress.email'                => trans('fi.mail-from-required'),
            'setting.dashboardWidgetsFromDate.required_if' => trans('fi.dashboard-widget-from-date-required'),
            'setting.dashboardWidgetsToDate.required_if'   => trans('fi.dashboard-widget-to-date-required'),
        ];
    }
}