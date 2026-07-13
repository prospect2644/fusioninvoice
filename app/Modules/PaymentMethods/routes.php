<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

Route::group(['middleware' => ['web', 'auth.admin'], 'namespace' => 'FI\Modules\PaymentMethods\Controllers'], function ()
{
    Route::get('payment_methods', ['uses' => 'PaymentMethodController@index', 'as' => 'paymentMethods.index'])->middleware('can:payment_methods.view');
    Route::get('payment_methods/create', ['uses' => 'PaymentMethodController@create', 'as' => 'paymentMethods.create'])->middleware('can:payment_methods.create');
    Route::get('payment_methods/{paymentMethod}/edit', ['uses' => 'PaymentMethodController@edit', 'as' => 'paymentMethods.edit'])->middleware('can:payment_methods.update');
    Route::get('payment_methods/{paymentMethod}/delete', ['uses' => 'PaymentMethodController@delete', 'as' => 'paymentMethods.delete'])->middleware('can:payment_methods.delete');

    Route::post('payment_methods', ['uses' => 'PaymentMethodController@store', 'as' => 'paymentMethods.store'])->middleware('can:payment_methods.create');
    Route::post('payment_methods/{paymentMethod}', ['uses' => 'PaymentMethodController@update', 'as' => 'paymentMethods.update'])->middleware('can:payment_methods.update');
});