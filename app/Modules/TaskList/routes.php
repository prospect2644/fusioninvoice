<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

Route::group(['middleware' => ['web', 'auth.admin'], 'prefix' => 'task', 'namespace' => 'FI\Modules\TaskList\Controllers'], function ()
{
    Route::group(['prefix' => 'widget'], function ()
    {
        Route::get('create', ['uses' => 'TaskController@createWidget', 'as' => 'task.widget.create']);
        Route::post('create', ['uses' => 'TaskController@storeWidget', 'as' => 'task.widget.store']);
        Route::get('list', ['uses' => 'TaskController@taskList', 'as' => 'task.widget.list']);
        Route::get('edit/{id}', ['uses' => 'TaskController@editWidget', 'as' => 'task.widget.edit']);
        Route::post('edit/{id}', ['uses' => 'TaskController@updateWidget', 'as' => 'task.widget.update']);
        Route::post('reorder', ['uses' => 'TaskController@reorder', 'as' => 'task.widget.reorder']);
        Route::post('refresh', ['uses' => 'TaskController@refresh', 'as' => 'task.widget.refresh']);
        Route::post('sort', ['uses' => 'TaskController@orderBy', 'as' => 'task.widget.sort']);
    });
    Route::get('{id}/delete', ['uses' => 'TaskController@delete', 'as' => 'task.delete']);
    Route::post('complete/{id}/{complete}', ['uses' => 'TaskController@completeToggle', 'as' => 'task.complete']);
    Route::get('/', ['uses' => 'TaskController@index', 'as' => 'task.index']);
    Route::get('create', ['uses' => 'TaskController@create', 'as' => 'task.create']);
    Route::post('create', ['uses' => 'TaskController@store', 'as' => 'task.store']);
    Route::get('edit/{id}', ['uses' => 'TaskController@edit', 'as' => 'task.edit']);
    Route::post('edit/{id}', ['uses' => 'TaskController@update', 'as' => 'task.update']);
    Route::get('{id}/show', ['uses' => 'TaskController@show', 'as' => 'task.show']);
});