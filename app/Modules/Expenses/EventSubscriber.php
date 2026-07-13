<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Modules\Expenses;

use FI\Modules\Expenses\Events\AddTransition;
use FI\Modules\Transitions\Models\Transitions;

class EventSubscriber
{

    public function addTransition(AddTransition $event)
    {
        $transition                      = new Transitions();
        $transition->user_id             = auth()->user()->id;
        $transition->client_id           = $event->expense->client_id;
        $transition->transitionable_id   = $event->expense->id;
        $transition->transitionable_type = 'FI\Modules\Expenses\Models\Expense';
        $transition->action_type         = $event->actionType;
        if (!empty($event->detail))
        {
            $transition->detail = json_encode($event->detail);
        }
        $transition->previous_value = $event->previousValue;
        $transition->current_value  = $event->currentValue;
        $transition->save();

    }

    public function subscribe($events)
    {
        $events->listen('FI\Modules\Expenses\Events\AddTransition', 'FI\Modules\Expenses\EventSubscriber@addTransition');
    }
}
