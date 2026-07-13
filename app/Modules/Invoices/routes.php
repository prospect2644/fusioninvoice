<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

Route::group(['middleware' => ['web', 'auth.admin'], 'namespace' => 'FI\Modules\Invoices\Controllers'], function ()
{
    Route::group(['prefix' => 'invoices'], function ()
    {
        Route::get('/', ['uses' => 'InvoiceController@index', 'as' => 'invoices.index'])->middleware('can:invoices.view');
        Route::get('ajax/filter-tags', ['uses' => 'InvoiceController@showFilterTags', 'as' => 'invoice.filterTags'])->middleware('can:invoices.view');
        Route::get('create', ['uses' => 'InvoiceCreateController@create', 'as' => 'invoices.create'])->middleware('can:invoices.create');
        Route::post('create', ['uses' => 'InvoiceCreateController@store', 'as' => 'invoices.store'])->middleware('can:invoices.create');
        Route::get('{id}/edit', ['uses' => 'InvoiceEditController@edit', 'as' => 'invoices.edit'])->middleware('can:invoices.update');
        Route::post('{id}/edit', ['uses' => 'InvoiceEditController@update', 'as' => 'invoices.update'])->middleware('can:invoices.update');
        Route::get('{id}/delete', ['uses' => 'InvoiceController@delete', 'as' => 'invoices.delete'])->middleware('can:invoices.delete');
        Route::get('{id}/pdf', ['uses' => 'InvoiceController@pdf', 'as' => 'invoices.pdf'])->middleware('can:invoices.view');
        Route::get('{id}/save-pdf', ['uses' => 'InvoiceController@savePdf', 'as' => 'invoices.save.pdf'])->middleware('can:invoices.view');
        Route::get('{id}/print', ['uses' => 'InvoiceController@printPdf', 'as' => 'invoices.print'])->middleware('can:invoices.view');
        Route::get('{id}/edit/refresh', ['uses' => 'InvoiceEditController@refreshEdit', 'as' => 'invoiceEdit.refreshEdit'])->middleware('can:invoices.update');
        Route::post('edit/refresh_to', ['uses' => 'InvoiceEditController@refreshTo', 'as' => 'invoiceEdit.refreshTo'])->middleware('can:invoices.update');
        Route::post('edit/refresh_from', ['uses' => 'InvoiceEditController@refreshFrom', 'as' => 'invoiceEdit.refreshFrom'])->middleware('can:invoices.update');
        Route::post('edit/refresh_totals', ['uses' => 'InvoiceEditController@refreshTotals', 'as' => 'invoiceEdit.refreshTotals'])->middleware('can:invoices.update');
        Route::post('edit/update_client', ['uses' => 'InvoiceEditController@updateClient', 'as' => 'invoiceEdit.updateClient'])->middleware('can:invoices.update');
        Route::post('edit/update_company_profile', ['uses' => 'InvoiceEditController@updateCompanyProfile', 'as' => 'invoiceEdit.updateCompanyProfile'])->middleware('can:invoices.update');
        Route::post('recalculate', ['uses' => 'InvoiceRecalculateController@recalculate', 'as' => 'invoices.recalculate'])->middleware('can:invoices.update');
        Route::post('bulk/delete', ['uses' => 'InvoiceController@bulkDelete', 'as' => 'invoices.bulk.delete'])->middleware('can:invoices.delete');
        Route::post('bulk/status', ['uses' => 'InvoiceController@bulkStatus', 'as' => 'invoices.bulk.status'])->middleware('can:invoices.update');
        Route::get('bulk/download/pdf', ['uses' => 'InvoiceController@bulkPdf', 'as' => 'invoices.bulk.pdf'])->middleware('can:invoices.view');
        Route::get('bulk/save/pdf', ['uses' => 'InvoiceController@saveBulkPdf', 'as' => 'invoices.bulk.save.pdf'])->middleware('can:invoices.view');
        Route::get('{files}/bulk/print', ['uses' => 'InvoiceController@printBulkPdf', 'as' => 'invoices.bulk.print'])->middleware('can:invoices.view');
        Route::post('custom_field/{id?}/delete_image/{field_name?}', ['uses' => 'InvoiceEditController@deleteImage', 'as' => 'invoiceEdit.deleteImage'])->middleware('can:invoices.update');
    });

    Route::group(['prefix' => 'invoice_copy'], function ()
    {
        Route::post('create', ['uses' => 'InvoiceCopyController@create', 'as' => 'invoiceCopy.create'])->middleware('can:invoices.create');
        Route::post('store', ['uses' => 'InvoiceCopyController@store', 'as' => 'invoiceCopy.store'])->middleware('can:invoices.create');
    });

    Route::group(['prefix' => 'invoice_mail'], function ()
    {
        Route::post('create', ['uses' => 'InvoiceMailController@create', 'as' => 'invoiceMail.create'])->middleware('can:invoices.update');
        Route::post('store', ['uses' => 'InvoiceMailController@store', 'as' => 'invoiceMail.store'])->middleware('can:invoices.update');
    });

    Route::group(['prefix' => 'invoice_item'], function ()
    {
        Route::post('delete', ['uses' => 'InvoiceItemController@delete', 'as' => 'invoiceItem.delete'])->middleware('can:invoices.update');
    });

});