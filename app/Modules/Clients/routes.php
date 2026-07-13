<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

Route::group(['middleware' => ['web', 'auth.admin'], 'prefix' => 'clients', 'namespace' => 'FI\Modules\Clients\Controllers'], function ()
{
    Route::get('/', ['uses' => 'ClientController@index', 'as' => 'clients.index'])->middleware('can:clients.view');
    Route::get('create', ['uses' => 'ClientController@create', 'as' => 'clients.create'])->middleware('can:clients.create');
    Route::get('{id}/edit', ['uses' => 'ClientController@edit', 'as' => 'clients.edit'])->middleware('can:clients.update');
    Route::get('{id}', ['uses' => 'ClientController@show', 'as' => 'clients.show'])->middleware('can:clients.view');
    Route::get('{id}/delete', ['uses' => 'ClientController@delete', 'as' => 'clients.delete'])->middleware('can:clients.delete');
    Route::get('ajax/filter-tags', ['uses' => 'ClientController@showFilterTags', 'as' => 'clients.filterTags'])->middleware('can:clients.view');
    Route::get('ajax/invoice_summary/{id}/{currency_code}', ['uses' => 'ClientController@invoiceSummary', 'as' => 'clients.invoiceSummary'])->middleware('can:clients.view');

    Route::post('create', ['uses' => 'ClientController@store', 'as' => 'clients.store'])->middleware('can:clients.create');
    Route::post('ajax/modal_edit', ['uses' => 'ClientController@ajaxModalEdit', 'as' => 'clients.ajax.modalEdit'])->middleware('can:clients.update');
    Route::post('ajax/modal_lookup', ['uses' => 'ClientController@ajaxModalLookup', 'as' => 'clients.ajax.modalLookup'])->middleware('can:clients.view');
    Route::post('ajax/modal_update/{id}', ['uses' => 'ClientController@ajaxModalUpdate', 'as' => 'clients.ajax.modalUpdate'])->middleware('can:clients.update');
    Route::post('ajax/check_name', ['uses' => 'ClientController@ajaxCheckName', 'as' => 'clients.ajax.checkName'])->middleware('can:clients.view');
    Route::post('ajax/check_duplicate_name', ['uses' => 'ClientController@ajaxCheckDuplicateName', 'as' => 'clients.ajax.checkDuplicateName'])->middleware('can:clients.view');
    Route::post('{id}/edit', ['uses' => 'ClientController@update', 'as' => 'clients.update'])->middleware('can:clients.update');

    Route::post('custom_field/{id?}/delete_image/{field_name?}', ['uses' => 'ClientController@deleteImage', 'as' => 'clients.deleteImage'])->middleware('can:clients.update');

    Route::get('email/payment/receipt/{id?}', ['uses' => 'ClientController@emailPaymentReceiptStatus', 'as' => 'clients.emailPaymentReceipt'])->middleware('can:clients.view');

    Route::group(['prefix' => '{clientId}/contacts'], function ()
    {
        Route::get('create', ['uses' => 'ContactController@create', 'as' => 'clients.contacts.create'])->middleware('can:contacts.create');
        Route::post('create', ['uses' => 'ContactController@store', 'as' => 'clients.contacts.store'])->middleware('can:contacts.create');
        Route::get('edit/{contactId}', ['uses' => 'ContactController@edit', 'as' => 'clients.contacts.edit'])->middleware('can:contacts.update');
        Route::post('edit/{contactId}', ['uses' => 'ContactController@update', 'as' => 'clients.contacts.update'])->middleware('can:contacts.update');
        Route::post('delete', ['uses' => 'ContactController@delete', 'as' => 'clients.contacts.delete'])->middleware('can:contacts.delete');
        Route::post('default', ['uses' => 'ContactController@updateDefault', 'as' => 'clients.contacts.updateDefault'])->middleware('can:contacts.update');
    });
});