<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

Route::group(['middleware' => ['web', 'auth.admin'], 'prefix' => 'expenses', 'namespace' => 'FI\Modules\Expenses\Controllers'], function ()
{
    Route::get('/', ['uses' => 'ExpenseController@index', 'as' => 'expenses.index'])->middleware('can:expenses.view');
    Route::get('create', ['uses' => 'ExpenseCreateController@create', 'as' => 'expenses.create'])->middleware('can:expenses.create');
    Route::post('create', ['uses' => 'ExpenseCreateController@store', 'as' => 'expenses.store'])->middleware('can:expenses.create');
    Route::get('{id}/edit', ['uses' => 'ExpenseEditController@edit', 'as' => 'expenses.edit'])->middleware('can:expenses.update');
    Route::post('{id}/edit', ['uses' => 'ExpenseEditController@update', 'as' => 'expenses.update'])->middleware('can:expenses.update');
    Route::get('{id}/delete', ['uses' => 'ExpenseController@delete', 'as' => 'expenses.delete'])->middleware('can:expenses.delete');
    Route::post('custom_field/{id?}/delete_image/{field_name?}', ['uses' => 'ExpenseController@deleteImage', 'as' => 'expenses.deleteImage'])->middleware('can:expenses.update');

    Route::group(['prefix' => 'bill'], function ()
    {
        Route::post('create', ['uses' => 'ExpenseBillController@create', 'as' => 'expenseBill.create'])->middleware('can:expenses.create');
        Route::post('store', ['uses' => 'ExpenseBillController@store', 'as' => 'expenseBill.store'])->middleware('can:expenses.create');
    });

    Route::get('lookup/category', ['uses' => 'ExpenseLookupController@lookupCategory', 'as' => 'expenses.lookupCategory'])->middleware('can:expenses.create');
    Route::get('lookup/vendor', ['uses' => 'ExpenseLookupController@lookupVendor', 'as' => 'expenses.lookupVendor'])->middleware('can:expenses.create');

    Route::post('bulk/delete', ['uses' => 'ExpenseController@bulkDelete', 'as' => 'expenses.bulk.delete'])->middleware('can:expenses.delete');

    Route::group(['prefix' => 'categories'], function ()
    {
        Route::get('/', ['uses' => 'ExpenseCategoryController@index', 'as' => 'expenses.categories.index'])->middleware('can:expense_categories.view');
        Route::get('create', ['uses' => 'ExpenseCategoryController@create', 'as' => 'expenses.categories.create'])->middleware('can:expense_categories.create');
        Route::post('create', ['uses' => 'ExpenseCategoryController@store', 'as' => 'expenses.categories.store'])->middleware('can:expense_categories.create');
        Route::get('{id}/edit', ['uses' => 'ExpenseCategoryController@edit', 'as' => 'expenses.categories.edit'])->middleware('can:expense_categories.update');
        Route::post('{id}/edit', ['uses' => 'ExpenseCategoryController@update', 'as' => 'expenses.categories.update'])->middleware('can:expense_categories.update');
        Route::get('{id}/delete', ['uses' => 'ExpenseCategoryController@delete', 'as' => 'expenses.categories.delete'])->middleware('can:expense_categories.delete');
    });

    Route::group(['prefix' => 'vendors'], function ()
    {
        Route::get('/', ['uses' => 'ExpenseVendorController@index', 'as' => 'expenses.vendors.index'])->middleware('can:expense_vendors.view');
        Route::get('create', ['uses' => 'ExpenseVendorController@create', 'as' => 'expenses.vendors.create'])->middleware('can:expense_vendors.create');
        Route::post('create', ['uses' => 'ExpenseVendorController@store', 'as' => 'expenses.vendors.store'])->middleware('can:expense_vendors.create');
        Route::get('{id}/edit', ['uses' => 'ExpenseVendorController@edit', 'as' => 'expenses.vendors.edit'])->middleware('can:expense_vendors.update');
        Route::post('{id}/edit', ['uses' => 'ExpenseVendorController@update', 'as' => 'expenses.vendors.update'])->middleware('can:expense_vendors.update');
        Route::get('{id}/delete', ['uses' => 'ExpenseVendorController@delete', 'as' => 'expenses.vendors.delete'])->middleware('can:expense_vendors.delete');
    });

    Route::group(['prefix' => 'expense_copy'], function ()
    {
        Route::post('create', ['uses' => 'ExpenseCopyController@create', 'as' => 'expenseCopy.create'])->middleware('can:expenses.create');
        Route::post('store', ['uses' => 'ExpenseCopyController@store', 'as' => 'expenseCopy.store'])->middleware('can:expenses.create');
    });
});