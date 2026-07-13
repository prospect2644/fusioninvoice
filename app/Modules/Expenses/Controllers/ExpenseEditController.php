<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Modules\Expenses\Controllers;

use FI\Http\Controllers\Controller;
use FI\Modules\Clients\Models\Client;
use FI\Modules\CompanyProfiles\Models\CompanyProfile;
use FI\Modules\CustomFields\Support\CustomFieldsParser;
use FI\Modules\CustomFields\Support\CustomFieldsTransformer;
use FI\Modules\Expenses\Models\Expense;
use FI\Modules\Expenses\Models\ExpenseCategory;
use FI\Modules\Expenses\Models\ExpenseVendor;
use FI\Modules\Expenses\Requests\ExpenseRequest;
use FI\Modules\Mru\Events\MruLog;
use FI\Support\DateFormatter;
use FI\Support\NumberFormatter;
use FI\Traits\ReturnUrl;


class ExpenseEditController extends Controller
{
    use ReturnUrl;

    public function edit($id)
    {
        $expense = Expense::defaultQuery()->find($id);

        event(new MruLog(['module' => 'expenses', 'action' => 'edit', 'id' => $id, 'title' => $expense->id . ' ' . $expense->companyProfile->company]));

        return view('expenses.form')
            ->with('editMode', true)
            ->with('companyProfiles', CompanyProfile::getList())
            ->with('expense', $expense)
            ->with('expenseCategory', ExpenseCategory::getDropDownList())
            ->with('vendors', ExpenseVendor::getDropDownList())
            ->with('clients', Client::getDropDownList())
            ->with('customFields', CustomFieldsParser::getFields('expenses'));
    }

    public function update(ExpenseRequest $request, $id)
    {
        $record = $request->except('attachments', 'custom');

        $record['expense_date'] = DateFormatter::unformat($record['expense_date']);
        $record['tax']          = ($record['tax']) ? NumberFormatter::unformat($record['tax']) : 0;

        $expense = Expense::find($id);

        $expense->fill($record);

        $expense->save();

        // Save the custom fields.
        $customFieldData = CustomFieldsTransformer::transform(request('custom', []), 'expenses', $expense);
        $expense->custom->update($customFieldData);

        return redirect($this->getReturnUrl())
            ->with('alertSuccess', trans('fi.record_successfully_updated'));
    }

}