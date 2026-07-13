<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

Route::group(['middleware' => ['web', 'auth.admin'], 'namespace' => 'FI\Modules\Payments\Controllers'], function ()
{
    Route::get('payments', ['uses' => 'PaymentController@index', 'as' => 'payments.index'])->middleware('can:payments.view');
    Route::get('payments/create-payment', ['uses' => 'PaymentController@createPayment', 'as' => 'payments.createPayment'])->middleware('can:payments.create');
    Route::post('payments/store-payment', ['uses' => 'PaymentController@storePayment', 'as' => 'payments.storePayment'])->middleware('can:payments.create');
    Route::get('payments/edit-payment/{payment}', ['uses' => 'PaymentController@editPayment', 'as' => 'payments.editPayment'])->middleware('can:payments.update');
    Route::post('payments/capture-payment-detail', ['uses' => 'PaymentController@capturePaymentDetail', 'as' => 'payments.capturePaymentDetail'])->middleware('can:payments.create');
    Route::get('payments/fetch-invoices-list', ['uses' => 'PaymentController@fetchInvoicesList', 'as' => 'payments.fetchInvoicesList'])->middleware('can:payments.create');
    Route::post('payments/prepare-credit-applications/{creditMemo}', ['uses' => 'PaymentController@prepareCreditApplication', 'as' => 'payments.prepareCreditApplication'])->middleware('can:payments.create');
    Route::post('payments/store-credit-applications', ['uses' => 'PaymentController@storeCreditApplication', 'as' => 'payments.storeCreditApplication'])->middleware('can:payments.create');
    Route::post('payments/prepare-invoice-settlement-with-creditmemo/{invoice}', ['uses' => 'PaymentController@prepareInvoiceSettlementWithCreditMemo', 'as' => 'payments.prepareInvoiceSettlementWithCreditMemo'])->middleware('can:payments.create');
    Route::post('payments/store-invoice-settlement-with-creditmemo', ['uses' => 'PaymentController@storeInvoiceSettlementWithCreditMemo', 'as' => 'payments.storeInvoiceSettlementWithCreditMemo'])->middleware('can:payments.create');
    Route::post('payments/prepare-invoice-settlement-with-prepayment/{invoice}', ['uses' => 'PaymentController@prepareInvoiceSettlementWithPrePayment', 'as' => 'payments.prepareInvoiceSettlementWithPrePayment'])->middleware('can:payments.create');
    Route::post('payments/store-invoice-settlement-with-prepayment', ['uses' => 'PaymentController@storeInvoiceSettlementWithPrePayment', 'as' => 'payments.storeInvoiceSettlementWithPrePayment'])->middleware('can:payments.create');
    Route::post('payments/create', ['uses' => 'PaymentController@create', 'as' => 'payments.create'])->middleware('can:payments.create');
    Route::post('payments/store', ['uses' => 'PaymentController@store', 'as' => 'payments.store'])->middleware('can:payments.create');
    Route::get('payments/{payment}', ['uses' => 'PaymentController@edit', 'as' => 'payments.edit'])->middleware('can:payments.update');
    Route::get('payments/applications/{payment}', ['uses' => 'PaymentController@applications', 'as' => 'payments.applications'])->middleware('can:payments.view');
    Route::post('payments/{payment}', ['uses' => 'PaymentController@update', 'as' => 'payments.update'])->middleware('can:payments.update');

    Route::get('payments/{payment}/delete', ['uses' => 'PaymentController@delete', 'as' => 'payments.delete'])->middleware('can:payments.delete');

    Route::post('bulk/delete', ['uses' => 'PaymentController@bulkDelete', 'as' => 'payments.bulk.delete'])->middleware('can:payments.delete');

    Route::post('payments/custom_field/{id?}/delete_image/{field_name?}', ['uses' => 'PaymentController@deleteImage', 'as' => 'payments.deleteImage'])->middleware('can:payments.update');

    Route::group(['prefix' => 'payment_mail'], function ()
    {
        Route::post('create', ['uses' => 'PaymentMailController@create', 'as' => 'paymentMail.create'])->middleware('can:payments.update');
        Route::post('store', ['uses' => 'PaymentMailController@store', 'as' => 'paymentMail.store'])->middleware('can:payments.update');
    });

    Route::group(['prefix' => 'invoice/{invoiceId}/payments'], function ()
    {
        Route::get('edit/{paymentInvoiceId}', ['uses' => 'PaymentController@editInvoicePayment', 'as' => 'invoices.payments.edit'])->middleware('can:payments.update');
        Route::post('edit/{paymentId}', ['uses' => 'PaymentController@updateInvoicePayment', 'as' => 'invoices.payments.update'])->middleware('can:payments.update');
        Route::post('delete', ['uses' => 'PaymentController@deleteInvoicePayment', 'as' => 'invoices.payments.delete'])->middleware('can:payments.delete');
    });
});