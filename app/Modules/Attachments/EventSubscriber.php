<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Modules\Attachments;

use FI\Modules\Attachments\Events\AddTransition;
use FI\Modules\Attachments\Events\CheckAttachment;
use FI\Modules\Transitions\Models\Transitions;

class EventSubscriber
{
    public function checkAttachment(CheckAttachment $event)
    {
        if (request()->hasFile('attachments'))
        {
            foreach (request()->file('attachments') as $attachment)
            {
                if ($attachment)
                {
                    $userId   = auth()->user()->id;
                    $filename = str_replace(',', '-', $attachment->getClientOriginalName());
                    $response = $event->object->attachments()->create([
                        'user_id'  => $userId,
                        'filename' => $filename,
                        'mimetype' => $attachment->getMimeType(),
                        'size'     => $attachment->getSize(),
                        'content'  => file_get_contents($attachment->path()),
                    ]);

                    $transition                      = new Transitions();
                    $transition->user_id             = $userId;
                    $transition->client_id           = isset($event->object->client_id) ? $event->object->client_id : null;
                    $transition->transitionable_id   = $response->id;
                    $transition->transitionable_type = 'FI\Modules\Attachments\Models\Attachment';
                    $transition->action_type         = 'created';
                    $transition->detail              = json_encode([
                        'filename' => $filename,
                    ]);
                    $transition->save();
                }

            }
        }
    }

    public function addTransition(AddTransition $event)
    {
        $transition                      = new Transitions();
        $transition->user_id             = auth()->user()->id;
        $transition->client_id           = isset($event->attachment->attachable->client_id) ? $event->attachment->attachable->client_id : null;
        $transition->transitionable_id   = $event->attachment->id;
        $transition->transitionable_type = 'FI\Modules\Attachments\Models\Attachment';
        $transition->action_type         = $event->actionType;
        $transition->detail              = json_encode($event->detail);
        $transition->save();

    }

    public function subscribe($events)
    {
        $events->listen('FI\Modules\Attachments\Events\AddTransition', 'FI\Modules\Attachments\EventSubscriber@AddTransition');
        $events->listen('FI\Modules\Attachments\Events\CheckAttachment', 'FI\Modules\Attachments\EventSubscriber@checkAttachment');
    }
}