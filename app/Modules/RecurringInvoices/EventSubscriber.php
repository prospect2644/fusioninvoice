<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Modules\RecurringInvoices;

use Carbon\Carbon;
use FI\Modules\RecurringInvoices\Events\AddTransition;
use FI\Modules\RecurringInvoices\Events\RecurringInvoiceModified;
use FI\Modules\RecurringInvoices\Support\RecurringInvoiceCalculate;
use FI\Modules\Transitions\Models\Transitions;

class EventSubscriber
{
    public function addTransition(AddTransition $event)
    {
        $userId = isset(auth()->user()->id) ? auth()->user()->id : $event->userId;

        $transitionableType = 'FI\Modules\RecurringInvoices\Models\RecurringInvoice';

        if (Carbon::parse($event->recurringInvoice->created_at)->diffInMinutes(Carbon::now()) <= 60 && $event->actionType == 'updated')
        {
            $event->actionType = 'created';
        }

        $transitions = Transitions::whereUserId($userId)
            ->whereClientId($event->recurringInvoice->client_id)
            ->whereTransitionableId($event->recurringInvoice->id)
            ->whereTransitionableType($transitionableType)
            ->whereActionType($event->actionType)
            ->whereDate('created_at', Carbon::today())->orderBy('id', 'DESC')->get();

        if ($transitions->count() > 0)
        {
            if (!empty($event->detail))
            {
                if ($event->actionType != 'created')
                {
                    $detail                        = json_decode($transitions[0]->detail);
                    $action_count                  = isset($detail->action_count) ? $detail->action_count + 1 : $transitions->count() + 1;
                    $event->detail['action_count'] = $action_count;
                }
                Transitions::whereId($transitions[0]->id)->update(['detail' => json_encode($event->detail)]);
            }
        }
        else
        {
            $transition                      = new Transitions();
            $transition->user_id             = $userId;
            $transition->client_id           = $event->recurringInvoice->client_id;
            $transition->transitionable_id   = $event->recurringInvoice->id;
            $transition->transitionable_type = $transitionableType;
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

    public function recurringInvoiceModified(RecurringInvoiceModified $event)
    {
        $recurringInvoiceCalculate = new RecurringInvoiceCalculate();

        $recurringInvoiceCalculate->calculate($event->recurringInvoice->id);
    }

    public function subscribe($events)
    {
        $events->listen('FI\Modules\RecurringInvoices\Events\AddTransition', 'FI\Modules\RecurringInvoices\EventSubscriber@addTransition');
        $events->listen('FI\Modules\RecurringInvoices\Events\RecurringInvoiceModified', 'FI\Modules\RecurringInvoices\EventSubscriber@recurringInvoiceModified');
    }
}