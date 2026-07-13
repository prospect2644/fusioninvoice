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
use FI\Modules\Expenses\Models\ExpenseCategory;
use FI\Modules\Expenses\Requests\ExpenseCategoryRequest;
use FI\Traits\ReturnUrl;

class ExpenseCategoryController extends Controller
{
    use ReturnUrl;

    public function index()
    {
        $this->setReturnUrl();

        return view('expenses.categories.index')
            ->with('expenseCategories', ExpenseCategory::orderBy('name')->paginate(config('fi.resultsPerPage')));
    }

    public function create()
    {
        return view('expenses.categories.form');
    }

    public function store(ExpenseCategoryRequest $request)
    {
        ExpenseCategory::create($request->all());

        return redirect($this->getReturnUrl())
            ->with('alertSuccess', trans('fi.record_successfully_created'));
    }

    public function edit($id)
    {
        return view('expenses.categories.form')
            ->with('expenseCategory', ExpenseCategory::find($id));
    }

    public function update(ExpenseCategoryRequest $request, $id)
    {
        $expenseCategory = ExpenseCategory::find($id);

        $expenseCategory->fill($request->all());

        $expenseCategory->save();

        return redirect($this->getReturnUrl())
            ->with('alertSuccess', trans('fi.record_successfully_updated'));
    }

    public function delete($id)
    {
        ExpenseCategory::destroy($id);

        return redirect($this->getReturnUrl())
            ->with('alert', trans('fi.record_successfully_deleted'));
    }
}