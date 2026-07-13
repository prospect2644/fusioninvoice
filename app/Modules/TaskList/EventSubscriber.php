<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Modules\TaskList;

use FI\Modules\Notifications\Models\Notification;
use FI\Modules\TaskList\Events\AddTransition;
use FI\Modules\TaskList\Events\AddNotification;
use FI\Modules\Transitions\Models\Transitions;

class EventSubscriber
{

    public function addTransition(AddTransition $event)
    {
        if ($event->task->client_id)
        {
            $transition                      = new Transitions();
            $transition->user_id             = auth()->user()->id;
            $transition->client_id           = $event->task->client_id;
            $transition->transitionable_id   = $event->task->id;
            $transition->transitionable_type = 'FI\Modules\TaskList\Models\Task';
            $transition->action_type         = $event->actionType;
            if (!empty($event->detail))
            {
                $transition->detail = json_encode($event->detail);
            }
            $transition->previous_value = $event->previousValue;
            $transition->current_value  = $event->currentValue;
            $transition->save();
        }

    }

    public function addNotification(AddNotification $event)
    {
        if (($event->task->assignee_id) && ($event->task->assignee_id != auth()->user()->id))
        {
            $notification                  = new Notification();
            $notification->user_id         = $event->task->assignee_id;
            $notification->notifiable_id   = $event->task->id;
            $notification->notifiable_type = 'FI\Modules\TaskList\Models\Task';
            $notification->action_type     = $event->actionType;
            if (!empty($event->detail))
            {
                $notification->detail = json_encode($event->detail);
            }
            $notification->save();
        }

    }

    public function subscribe($events)
    {
        $events->listen('FI\Modules\TaskList\Events\AddTransition', 'FI\Modules\TaskList\EventSubscriber@addTransition');
        $events->listen('FI\Modules\TaskList\Events\AddNotification', 'FI\Modules\TaskList\EventSubscriber@addNotification');
    }
}
