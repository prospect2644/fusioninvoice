<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

Route::group(['middleware' => ['web']], function ()
{
    Route::get('tasks/run', ['uses' => 'FI\Modules\Tasks\Controllers\TaskController@run', 'as' => 'tasks.run']);
    Route::get('tasks/generate-timeline-history', ['uses' => 'FI\Modules\Tasks\Controllers\TaskController@generateTimelineHistory', 'as' => 'tasks.generate_timeline_history']);
});
