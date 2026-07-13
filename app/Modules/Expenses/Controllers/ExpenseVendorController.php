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
use FI\Modules\Expenses\Models\ExpenseVendor;
use FI\Modules\Expenses\Requests\ExpenseVendorRequest;
use FI\Traits\ReturnUrl;

class ExpenseVendorController extends Controller
{
    use ReturnUrl;

    public function index()
    {
        $this->setReturnUrl();

        return view('expenses.vendors.index')
            ->with('expenseVendors', ExpenseVendor::orderBy('name')->paginate(config('fi.resultsPerPage')));
    }

    public function create()
    {
        return view('expenses.vendors.form');
    }

    public function store(ExpenseVendorRequest $request)
    {
        ExpenseVendor::create($request->all());

        return redirect($this->getReturnUrl())
            ->with('alertSuccess', trans('fi.record_successfully_created'));
    }

    public function edit($id)
    {
        return view('expenses.vendors.form')
            ->with('expenseVendor', ExpenseVendor::find($id));
    }

    public function update(ExpenseVendorRequest $request, $id)
    {
        $expenseVendor = ExpenseVendor::find($id);

        $expenseVendor->fill($request->all());

        $expenseVendor->save();

        return redirect($this->getReturnUrl())
            ->with('alertSuccess', trans('fi.record_successfully_updated'));
    }

    public function delete($id)
    {
        ExpenseVendor::destroy($id);

        return redirect($this->getReturnUrl())
            ->with('alert', trans('fi.record_successfully_deleted'));
    }
}