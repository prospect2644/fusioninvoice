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
use FI\Modules\CompanyProfiles\Models\CompanyProfile;
use FI\Modules\CustomFields\Models\ExpenseCustom;
use FI\Modules\Expenses\Events\AddTransition;
use FI\Modules\Expenses\Models\Expense;
use FI\Modules\Expenses\Models\ExpenseCategory;
use FI\Modules\Expenses\Models\ExpenseVendor;
use FI\Traits\ReturnUrl;
use Illuminate\Support\Facades\Storage;

class ExpenseController extends Controller
{
    use ReturnUrl;

    public function index()
    {
        $this->setReturnUrl();

        $expenses = Expense::defaultQuery()
            ->keywords(request('search'))
            ->leftJoin('expenses_custom', 'expenses_custom.expense_id', '=', 'expenses.id')
            ->categoryId(request('category'))
            ->vendorId(request('vendor'))
            ->status(request('status'))
            ->companyProfileId(request('company_profile'))
            ->sortable(['expense_date' => 'desc'])
            ->paginate(config('fi.defaultNumPerPage'));

        return view('expenses.index')
            ->with('expenses', $expenses)
            ->with('searchPlaceholder', trans('fi.search_expenses'))
            ->with('categories', ['' => trans('fi.all_categories')] + ExpenseCategory::getList())
            ->with('vendors', ['' => trans('fi.all_vendors')] + ExpenseVendor::getList())
            ->with('statuses', ['' => trans('fi.all_statuses'), 'billed' => trans('fi.billed'), 'not_billed' => trans('fi.not_billed'), 'not_billable' => trans('fi.not_billable')])
            ->with('companyProfiles', ['' => trans('fi.all_company_profiles')] + CompanyProfile::getList());
    }

    public function delete($id)
    {
        $expense = Expense::find($id);
        if (!empty($expense->client_id))
        {
            event(new AddTransition($expense, 'deleted'));
        }
        $expense->delete();

        return redirect($this->getReturnUrl())
            ->with('alertSuccess', trans('fi.record_successfully_deleted'));
    }

    public function bulkDelete()
    {
        $expenses = Expense::whereIn('id', request('ids'))->get();
        foreach ($expenses as $expense)
        {
            if (!empty($expense->client_id))
            {
                event(new AddTransition($expense, 'deleted'));
            }
        }
        Expense::destroy(request('ids'));
    }

    public function deleteImage($id, $columnName)
    {
        $customFields = ExpenseCustom::whereExpenseId($id)->first();

        $existingFile = 'expenses' . DIRECTORY_SEPARATOR . $customFields->{$columnName};
        if (Storage::disk(CustomFieldsTransformer::STORAGE_DISK_NAME)->exists($existingFile))
        {
            try
            {
                Storage::disk(CustomFieldsTransformer::STORAGE_DISK_NAME)->delete($existingFile);
                $customFields->{$columnName} = null;
                $customFields->save();
            }
            catch (Exception $e)
            {

            }
        }
    }
}