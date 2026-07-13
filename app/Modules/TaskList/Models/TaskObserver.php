<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Modules\TaskList\Models;

use FI\Modules\Attachments\Events\CheckAttachment;
use FI\Modules\Notifications\Models\Notification;
use FI\Modules\TaskList\Events\AddNotification;
use FI\Modules\TaskList\Events\AddTransition;

class TaskObserver
{
    public function deleted(Task $task)
    {
        foreach ($task->attachments as $attachment)
        {
            $attachment->delete();
        }

        foreach ($task->notifications as $notification)
        {
            $notification->delete();
        }
    }

    public function created(Task $task)
    {
        event(new AddTransition($task, 'created'));
        event(new AddNotification($task, 'created'));
    }

    public function updating(Task $task)
    {
        if ($task->isDirty('is_complete') && $task->is_complete)
        {
            Notification::whereNotifiableId($task->id)->whereNotifiableType('FI\Modules\TaskList\Models\Task')->update(['is_viewed' => 1]);
            event(new AddTransition($task, 'completed'));
        }
        else
        {
            event(new AddTransition($task, 'updated'));
        }
    }

    public function saved(Task $task)
    {
        event(new CheckAttachment($task));
    }
}