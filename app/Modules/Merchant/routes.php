<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

Route::group(['prefix' => 'merchant', 'middleware' => 'web', 'namespace' => 'FI\Modules\Merchant\Controllers'], function ()
{
    Route::get('pay/stripe/{urlKey}', ['uses' => 'StripeController@pay', 'as' => 'merchant.pay.stripe']);
    Route::get('pay/stripe/return/{urlKey}', ['uses' => 'StripeController@success', 'as' => 'merchant.pay.stripe.return']);
    Route::get('pay/stripe/cancel/{urlKey}', ['uses' => 'StripeController@cancel', 'as' => 'merchant.pay.stripe.cancel']);

    Route::get('pay/paypal/{urlKey}', ['uses' => 'PayPalController@pay', 'as' => 'merchant.pay.paypal']);
    Route::get('pay/paypal/return/{urlKey}', ['uses' => 'PayPalController@success', 'as' => 'merchant.pay.paypal.return']);
    Route::get('pay/paypal/cancel/{urlKey}', ['uses' => 'PayPalController@cancel', 'as' => 'merchant.pay.paypal.cancel']);

    Route::get('pay/mollie/{urlKey}', ['uses' => 'MollieController@pay', 'as' => 'merchant.pay.mollie']);
    Route::get('pay/mollie/return/{urlKey}', ['uses' => 'MollieController@success', 'as' => 'merchant.pay.mollie.return']);
    Route::post('pay/mollie/webhook/{urlKey}', ['uses' => 'MollieController@webhook', 'as' => 'merchant.pay.mollie.webhook']);
    Route::get('pay/mollie/cancel/{urlKey}', ['uses' => 'MollieController@cancel', 'as' => 'merchant.pay.mollie.cancel']);
});