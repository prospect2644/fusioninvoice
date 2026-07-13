<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

Route::group(['middleware' => ['web', 'auth.admin'], 'namespace' => 'FI\Modules\TaxRates\Controllers'], function ()
{
    Route::get('tax_rates', ['uses' => 'TaxRateController@index', 'as' => 'taxRates.index'])->middleware('can:tax_rates.view');
    Route::get('tax_rates/create', ['uses' => 'TaxRateController@create', 'as' => 'taxRates.create'])->middleware('can:tax_rates.create');
    Route::get('tax_rates/{taxRate}/edit', ['uses' => 'TaxRateController@edit', 'as' => 'taxRates.edit'])->middleware('can:tax_rates.update');
    Route::get('tax_rates/{taxRate}/delete', ['uses' => 'TaxRateController@delete', 'as' => 'taxRates.delete'])->middleware('can:tax_rates.delete');

    Route::post('tax_rates', ['uses' => 'TaxRateController@store', 'as' => 'taxRates.store'])->middleware('can:tax_rates.create');
    Route::post('tax_rates/{taxRate}', ['uses' => 'TaxRateController@update', 'as' => 'taxRates.update'])->middleware('can:tax_rates.update');
});