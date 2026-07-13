<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

Route::group(['prefix' => 'system_log', 'middleware' => 'web', 'namespace' => 'FI\Modules\SystemLog\Controllers'], function ()
{
    Route::get('/', ['uses' => 'SystemLogController@index', 'as' => 'systemLog.index'])->middleware('can:system_logs.view');
});