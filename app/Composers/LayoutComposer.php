<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Composers;

use FI\Modules\Currencies\Models\Currency;
use FI\Modules\Notifications\Models\Notification;

class LayoutComposer
{
    public function compose($view)
    {
        $notifications = Notification::select('*')
            ->with('notifiable')
            ->userId(auth()->user()->id)
            ->where('is_viewed', 0)
            ->sortable(['created_at' => 'desc'])
            ->get();
        $view->with('allCurrencies', Currency::all()->toArray());
        $view->with('userName', auth()->user()->name);
        $view->with('notifications', $notifications);
        $view->with('profileImageUrl', profileImageUrl(auth()->user()));
        $view->with('urlSegment1', request()->segment(1));
        $view->with('urlSegment2', request()->segment(2));
    }
}