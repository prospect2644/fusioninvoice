<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

Route::group(['middleware' => ['web', 'auth.admin'], 'namespace' => 'FI\Modules\Quotes\Controllers'], function ()
{
    Route::group(['prefix' => 'quotes'], function ()
    {
        Route::get('/', ['uses' => 'QuoteController@index', 'as' => 'quotes.index'])->middleware('can:quotes.view');
        Route::get('create', ['uses' => 'QuoteCreateController@create', 'as' => 'quotes.create'])->middleware('can:quotes.create');
        Route::post('create', ['uses' => 'QuoteCreateController@store', 'as' => 'quotes.store'])->middleware('can:quotes.create');
        Route::get('{id}/edit', ['uses' => 'QuoteEditController@edit', 'as' => 'quotes.edit'])->middleware('can:quotes.update');
        Route::post('{id}/edit', ['uses' => 'QuoteEditController@update', 'as' => 'quotes.update'])->middleware('can:quotes.update');
        Route::get('{id}/delete', ['uses' => 'QuoteController@delete', 'as' => 'quotes.delete'])->middleware('can:quotes.delete');
        Route::get('{id}/pdf', ['uses' => 'QuoteController@pdf', 'as' => 'quotes.pdf'])->middleware('can:quotes.view');
        Route::get('{id}/save-pdf', ['uses' => 'QuoteController@savePdf', 'as' => 'quotes.save.pdf'])->middleware('can:quotes.view');
        Route::get('{id}/print', ['uses' => 'QuoteController@printPdf', 'as' => 'quotes.print'])->middleware('can:quotes.view');
        Route::get('{id}/edit/refresh', ['uses' => 'QuoteEditController@refreshEdit', 'as' => 'quoteEdit.refreshEdit'])->middleware('can:quotes.view');
        Route::post('edit/refresh_to', ['uses' => 'QuoteEditController@refreshTo', 'as' => 'quoteEdit.refreshTo'])->middleware('can:quotes.view');
        Route::post('edit/refresh_from', ['uses' => 'QuoteEditController@refreshFrom', 'as' => 'quoteEdit.refreshFrom'])->middleware('can:quotes.view');
        Route::post('edit/refresh_totals', ['uses' => 'QuoteEditController@refreshTotals', 'as' => 'quoteEdit.refreshTotals'])->middleware('can:quotes.view');
        Route::post('edit/update_client', ['uses' => 'QuoteEditController@updateClient', 'as' => 'quoteEdit.updateClient'])->middleware('can:quotes.update');
        Route::post('edit/update_company_profile', ['uses' => 'QuoteEditController@updateCompanyProfile', 'as' => 'quoteEdit.updateCompanyProfile'])->middleware('can:quotes.update');
        Route::post('recalculate', ['uses' => 'QuoteRecalculateController@recalculate', 'as' => 'quotes.recalculate'])->middleware('can:quotes.update');
        Route::post('bulk/delete', ['uses' => 'QuoteController@bulkDelete', 'as' => 'quotes.bulk.delete'])->middleware('can:quotes.delete');
        Route::post('bulk/status', ['uses' => 'QuoteController@bulkStatus', 'as' => 'quotes.bulk.status'])->middleware('can:quotes.update');
        Route::get('bulk/download/pdf', ['uses' => 'QuoteController@bulkPdf', 'as' => 'quotes.bulk.pdf'])->middleware('can:quotes.view');
        Route::get('bulk/save/pdf', ['uses' => 'QuoteController@saveBulkPdf', 'as' => 'quotes.bulk.save.pdf'])->middleware('can:quotes.view');
        Route::get('{files}/bulk/print', ['uses' => 'QuoteController@printBulkPdf', 'as' => 'quotes.bulk.print'])->middleware('can:quotes.view');
        Route::post('custom_field/{id?}/delete_image/{field_name?}', ['uses' => 'QuoteEditController@deleteImage', 'as' => 'quoteEdit.deleteImage'])->middleware('can:quotes.update');
    });

    Route::group(['prefix' => 'quote_copy'], function ()
    {
        Route::post('create', ['uses' => 'QuoteCopyController@create', 'as' => 'quoteCopy.create'])->middleware('can:quotes.create');
        Route::post('store', ['uses' => 'QuoteCopyController@store', 'as' => 'quoteCopy.store'])->middleware('can:quotes.create');
    });

    Route::group(['prefix' => 'quote_to_invoice'], function ()
    {
        Route::post('create', ['uses' => 'QuoteToInvoiceController@create', 'as' => 'quoteToInvoice.create'])->middleware('can:invoices.create');
        Route::post('store', ['uses' => 'QuoteToInvoiceController@store', 'as' => 'quoteToInvoice.store'])->middleware('can:invoices.create');
    });

    Route::group(['prefix' => 'quote_mail'], function ()
    {
        Route::post('create', ['uses' => 'QuoteMailController@create', 'as' => 'quoteMail.create'])->middleware('can:quotes.update');
        Route::post('store', ['uses' => 'QuoteMailController@store', 'as' => 'quoteMail.store'])->middleware('can:quotes.update');
    });

    Route::group(['prefix' => 'quote_item'], function ()
    {
        Route::post('delete', ['uses' => 'QuoteItemController@delete', 'as' => 'quoteItem.delete'])->middleware('can:quotes.update');
    });
});