<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

Route::group(['middleware' => ['web', 'auth.admin'], 'namespace' => 'FI\Modules\Users\Controllers'], function ()
{
    Route::get('users', ['uses' => 'UserController@index', 'as' => 'users.index'])->middleware('can:users.view');

    Route::get('users/create/{userType}', ['uses' => 'UserController@create', 'as' => 'users.create'])->middleware('can:users.create');
    Route::post('users/create', ['uses' => 'UserController@store', 'as' => 'users.store'])->middleware('can:users.create');

    Route::get('users/{id}/edit/{userType}', ['uses' => 'UserController@edit', 'as' => 'users.edit'])->middleware('can:users.update');
    Route::post('users/{id}/edit', ['uses' => 'UserController@update', 'as' => 'users.update'])->middleware('can:users.update');

    Route::get('users/{id}/delete', ['uses' => 'UserController@delete', 'as' => 'users.delete'])->middleware('can:users.delete');

    Route::get('users/{id}/password/edit', ['uses' => 'UserPasswordController@edit', 'as' => 'users.password.edit'])->middleware('can:users.update');
    Route::post('users/{id}/password/edit', ['uses' => 'UserPasswordController@update', 'as' => 'users.password.update'])->middleware('can:users.update');

    Route::post('users/client', ['uses' => 'UserController@getClientInfo', 'as' => 'users.clientInfo'])->middleware('can:users.view');

    Route::post('users/custom_field/{id?}/delete_image/{field_name?}', ['uses' => 'UserController@deleteImage', 'as' => 'users.deleteImage'])->middleware('can:users.update');

    Route::get('users/{id}/permissions', ['uses' => 'UserController@getPermissions', 'as' => 'users.getPermissions'])->middleware('can:users.update');

    Route::get('users/{id}/inactive', ['uses' => 'UserController@updateStatus', 'as' => 'users.update-status'])->middleware('can:users.update');
});
