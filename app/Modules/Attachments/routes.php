<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

Route::group(['prefix' => 'attachments', 'middleware' => 'web', 'namespace' => 'FI\Modules\Attachments\Controllers'], function ()
{
    Route::get('{urlKey}/download', ['uses' => 'AttachmentController@download', 'as' => 'attachments.download']);

    Route::group(['middleware' > 'auth.admin'], function ()
    {
        Route::post('ajax/list', ['uses' => 'AttachmentController@ajaxList', 'as' => 'attachments.ajax.list'])->middleware('can:attachments.view');
        Route::post('ajax/delete', ['uses' => 'AttachmentController@ajaxDelete', 'as' => 'attachments.ajax.delete'])->middleware('can:attachments.delete');
        Route::post('ajax/modal', ['uses' => 'AttachmentController@ajaxModal', 'as' => 'attachments.ajax.modal'])->middleware('can:attachments.create');
        Route::post('ajax/upload', ['uses' => 'AttachmentController@ajaxUpload', 'as' => 'attachments.ajax.upload'])->middleware('can:attachments.create');
        Route::post('ajax/access/update', ['uses' => 'AttachmentController@ajaxAccessUpdate', 'as' => 'attachments.ajax.access.update'])->middleware('can:attachments.update');
    });
});