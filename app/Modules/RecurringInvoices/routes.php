<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

Route::group(['middleware' => ['web', 'auth.admin'], 'namespace' => 'FI\Modules\RecurringInvoices\Controllers'], function ()
{
    Route::group(['prefix' => 'recurring_invoices'], function ()
    {
        Route::get('/', ['uses' => 'RecurringInvoiceController@index', 'as' => 'recurringInvoices.index'])->middleware('can:recurring_invoices.view');
        Route::get('ajax/filter-tags', ['uses' => 'RecurringInvoiceController@showFilterTags', 'as' => 'recurringInvoice.filterTags'])->middleware('can:recurring_invoices.view');
        Route::get('create', ['uses' => 'RecurringInvoiceCreateController@create', 'as' => 'recurringInvoices.create'])->middleware('can:recurring_invoices.create');
        Route::post('create', ['uses' => 'RecurringInvoiceCreateController@store', 'as' => 'recurringInvoices.store'])->middleware('can:recurring_invoices.create');
        Route::get('{id}/edit', ['uses' => 'RecurringInvoiceEditController@edit', 'as' => 'recurringInvoices.edit'])->middleware('can:recurring_invoices.update');
        Route::post('{id}/edit', ['uses' => 'RecurringInvoiceEditController@update', 'as' => 'recurringInvoices.update'])->middleware('can:recurring_invoices.update');
        Route::get('{id}/delete', ['uses' => 'RecurringInvoiceController@delete', 'as' => 'recurringInvoices.delete'])->middleware('can:recurring_invoices.delete');

        Route::get('{id}/edit/refresh', ['uses' => 'RecurringInvoiceEditController@refreshEdit', 'as' => 'recurringInvoiceEdit.refreshEdit'])->middleware('can:recurring_invoices.update');
        Route::post('edit/refresh_to', ['uses' => 'RecurringInvoiceEditController@refreshTo', 'as' => 'recurringInvoiceEdit.refreshTo'])->middleware('can:recurring_invoices.update');
        Route::post('edit/refresh_from', ['uses' => 'RecurringInvoiceEditController@refreshFrom', 'as' => 'recurringInvoiceEdit.refreshFrom'])->middleware('can:recurring_invoices.update');
        Route::post('edit/refresh_totals', ['uses' => 'RecurringInvoiceEditController@refreshTotals', 'as' => 'recurringInvoiceEdit.refreshTotals'])->middleware('can:recurring_invoices.update');
        Route::post('edit/update_client', ['uses' => 'RecurringInvoiceEditController@updateClient', 'as' => 'recurringInvoiceEdit.updateClient'])->middleware('can:recurring_invoices.update');
        Route::post('edit/update_company_profile', ['uses' => 'RecurringInvoiceEditController@updateCompanyProfile', 'as' => 'recurringInvoiceEdit.updateCompanyProfile'])->middleware('can:recurring_invoices.update');
        Route::post('recalculate', ['uses' => 'RecurringInvoiceRecalculateController@recalculate', 'as' => 'recurringInvoices.recalculate'])->middleware('can:recurring_invoices.update');
        Route::post('custom_field/{id?}/delete_image/{field_name?}', ['uses' => 'RecurringInvoiceEditController@deleteImage', 'as' => 'recurringInvoiceEdit.deleteImage'])->middleware('can:recurring_invoices.update');
    });

    Route::group(['prefix' => 'recurring_invoice_copy'], function ()
    {
        Route::post('create', ['uses' => 'RecurringInvoiceCopyController@create', 'as' => 'recurringInvoiceCopy.create'])->middleware('can:recurring_invoices.create');
        Route::post('store', ['uses' => 'RecurringInvoiceCopyController@store', 'as' => 'recurringInvoiceCopy.store'])->middleware('can:recurring_invoices.create');
    });

    Route::group(['prefix' => 'recurring_invoice_item'], function ()
    {
        Route::post('delete', ['uses' => 'RecurringInvoiceItemController@delete', 'as' => 'recurringInvoiceItem.delete'])->middleware('can:recurring_invoices.update');
    });
});