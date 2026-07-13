<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

Route::group(['middleware' => ['web', 'auth.admin'], 'namespace' => 'FI\Modules\ItemLookups\Controllers'], function ()
{
    Route::get('item_lookups', ['uses' => 'ItemLookupController@index', 'as' => 'itemLookups.index'])->middleware('can:item_lookup.view');
    Route::get('item_lookups/create', ['uses' => 'ItemLookupController@create', 'as' => 'itemLookups.create'])->middleware('can:item_lookup.create');
    Route::get('item_lookups/{itemLookup}/edit', ['uses' => 'ItemLookupController@edit', 'as' => 'itemLookups.edit'])->middleware('can:item_lookup.update');
    Route::post('item_lookups/detail', ['uses' => 'ItemLookupController@getDetail', 'as' => 'itemLookups.getDetail']);
    Route::get('item_lookups/{itemLookup}/delete', ['uses' => 'ItemLookupController@delete', 'as' => 'itemLookups.delete'])->middleware('can:item_lookup.delete');

    Route::post('item_lookups', ['uses' => 'ItemLookupController@store', 'as' => 'itemLookups.store'])->middleware('can:item_lookup.create');
    Route::post('item_lookups/{itemLookup}', ['uses' => 'ItemLookupController@update', 'as' => 'itemLookups.update'])->middleware('can:item_lookup.update');
    Route::post('item_lookups/ajax/process', ['uses' => 'ItemLookupController@process', 'as' => 'itemLookups.ajax.process'])->middleware('can:item_lookup.update');

    Route::group(['prefix' => 'item/categories'], function ()
    {
        Route::get('/', ['uses' => 'ItemCategoryController@index', 'as' => 'item.categories.index'])->middleware('can:item_categories.view');
        Route::get('create', ['uses' => 'ItemCategoryController@create', 'as' => 'item.categories.create'])->middleware('can:item_categories.create');
        Route::post('create', ['uses' => 'ItemCategoryController@store', 'as' => 'item.categories.store'])->middleware('can:item_categories.create');
        Route::get('{id}/edit', ['uses' => 'ItemCategoryController@edit', 'as' => 'item.categories.edit'])->middleware('can:item_categories.update');
        Route::post('{id}/edit', ['uses' => 'ItemCategoryController@update', 'as' => 'item.categories.update'])->middleware('can:item_categories.update');
        Route::get('{id}/delete', ['uses' => 'ItemCategoryController@delete', 'as' => 'item.categories.delete'])->middleware('can:item_categories.delete');
    });
});
