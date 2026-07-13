<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

Route::group(['middleware' => ['web', 'auth.admin'], 'namespace' => 'FI\Modules\Currencies\Controllers'], function ()
{
    Route::get('currencies', ['uses' => 'CurrencyController@index', 'as' => 'currencies.index'])->middleware('can:currencies.view');
    Route::get('currencies/create', ['uses' => 'CurrencyController@create', 'as' => 'currencies.create'])->middleware('can:currencies.create');
    Route::get('currencies/{id}/edit', ['uses' => 'CurrencyController@edit', 'as' => 'currencies.edit'])->middleware('can:currencies.update');
    Route::get('currencies/{id}/delete', ['uses' => 'CurrencyController@delete', 'as' => 'currencies.delete'])->middleware('can:currencies.delete');

    Route::post('currencies', ['uses' => 'CurrencyController@store', 'as' => 'currencies.store'])->middleware('can:currencies.create');
    Route::post('currencies/get-exchange-rate', ['uses' => 'CurrencyController@getExchangeRate', 'as' => 'currencies.getExchangeRate'])->middleware('can:currencies.update');
    Route::post('currencies/{id}', ['uses' => 'CurrencyController@update', 'as' => 'currencies.update'])->middleware('can:currencies.update');

});