<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

Route::group(['prefix' => 'notes', 'middleware' => ['web', 'auth'], 'namespace' => 'FI\Modules\Notes\Controllers'], function ()
{
    Route::get('create', ['uses' => 'NoteController@create', 'as' => 'notes.create'])->middleware('can:notes.create');
    Route::get('{id}/edit', ['uses' => 'NoteController@edit', 'as' => 'notes.edit'])->middleware('can:notes.update');
    Route::get('list/{model}/{id}/{showPrivateCheckbox}/{description?}/{tags?}/{username?}', ['uses' => 'NoteController@listNotes', 'as' => 'notes.list'])->middleware('can:notes.view');
    Route::post('create', ['uses' => 'NoteController@store', 'as' => 'notes.store'])->middleware('can:notes.create');
    Route::post('{id}/edit', ['uses' => 'NoteController@update', 'as' => 'notes.update'])->middleware('can:notes.update');
    Route::post('delete', ['uses' => 'NoteController@delete', 'as' => 'notes.delete'])->middleware('can:notes.delete');
});