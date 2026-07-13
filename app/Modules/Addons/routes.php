<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

Route::group(['middleware' => ['web', 'auth.admin'], 'prefix' => 'addons', 'namespace' => 'FI\Modules\Addons\Controllers'], function ()
{
    Route::get('/', ['uses' => 'AddonController@index', 'as' => 'addons.index'])->middleware('can:addons.view');

    Route::get('install/{id}', ['uses' => 'AddonController@install', 'as' => 'addons.install'])->middleware('can:addons.create');
    Route::get('uninstall/{id}', ['uses' => 'AddonController@uninstall', 'as' => 'addons.uninstall'])->middleware('can:addons.delete');
    Route::get('upgrade/{id}', ['uses' => 'AddonController@upgrade', 'as' => 'addons.upgrade'])->middleware('can:addons.update');
});