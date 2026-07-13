<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

Route::group(['middleware' => ['web', 'auth.admin'], 'namespace' => 'FI\Modules\CustomFields\Controllers'], function ()
{
    Route::get('custom_fields', ['uses' => 'CustomFieldController@index', 'as' => 'customFields.index'])->middleware('can:custom_fields.view');
    Route::get('custom_fields/create', ['uses' => 'CustomFieldController@create', 'as' => 'customFields.create'])->middleware('can:custom_fields.create');
    Route::get('custom_fields/{id}/edit', ['uses' => 'CustomFieldController@edit', 'as' => 'customFields.edit'])->middleware('can:custom_fields.update');
    Route::get('custom_fields/{id}/delete', ['uses' => 'CustomFieldController@delete', 'as' => 'customFields.delete'])->middleware('can:custom_fields.delete');
    Route::post('custom_fields/bulk/delete', ['uses' => 'CustomFieldController@bulkDelete', 'as' => 'customFields.bulk.delete'])->middleware('can:custom_fields.delete');

    Route::post('custom_fields', ['uses' => 'CustomFieldController@store', 'as' => 'customFields.store'])->middleware('can:custom_fields.create');
    Route::post('custom_fields/{id}', ['uses' => 'CustomFieldController@update', 'as' => 'customFields.update'])->middleware('can:custom_fields.update');
    Route::post('custom_fields/reorder/store', ['uses' => 'CustomFieldController@reorder', 'as' => 'customFields.reorder'])->middleware('can:custom_fields.update');
});