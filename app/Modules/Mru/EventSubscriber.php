<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Modules\Mru;

use Carbon\Carbon;
use FI\Modules\Mru\Events\MruLog;
use FI\Modules\Mru\Models\Mru;
use Illuminate\Support\Facades\Request;

class EventSubscriber
{
    public function mruLog(MruLog $event)
    {
        $url = Request::url();

        // For module report we just have to redirect on report page without search
        if ($event->mruData['module'] == 'reports')
        {
            $url = Request::server('HTTP_REFERER');
        }

        // If user jump from client view to edit then we have to update view URL with edit
        if ($event->mruData['module'] == 'clients' && $event->mruData['action'] == 'edit')
        {
            $clientViewUrl = url('/') . '/' . $event->mruData['module'] . '/' . $event->mruData['id'];
            Mru::whereUserId(auth()->user()->id)->whereModule($event->mruData['module'])->whereUrl($clientViewUrl)->update([
                'url' => $url,
            ]);
        }
        elseif ($event->mruData['module'] == 'clients' && $event->mruData['action'] == 'view')
        {
            $clientViewUrl = url('/') . '/' . $event->mruData['module'] . '/' . $event->mruData['id'] . '/edit';
            Mru::whereUserId(auth()->user()->id)->whereModule($event->mruData['module'])->whereUrl($clientViewUrl)->update([
                'url' => $url,
            ]);
        }

        // Let's Insert MRU entry on database
        Mru::updateOrCreate([
            'user_id'    => auth()->user()->id,
            'module'     => $event->mruData['module'],
            'title'      => mb_strimwidth($event->mruData['title'], 0, 21, "..."),
            'url'        => $url,
            'element_id' => $event->mruData['id'],
        ], ['updated_at' => Carbon::now()]);

        Mru::whereUserId(auth()->user()->id)->latest()->take(Mru::count())->skip(10)->get()->each(function ($row)
        {
            $row->delete();
        });
    }

    public function subscribe($events)
    {
        $events->listen('FI\Modules\Mru\Events\MruLog', 'FI\Modules\Mru\EventSubscriber@mruLog');
    }
}