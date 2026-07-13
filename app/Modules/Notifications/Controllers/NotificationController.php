<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Modules\Notifications\Controllers;

use FI\Http\Controllers\Controller;
use FI\Modules\Notifications\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function markViewed(Request $request, Notification $notification)
    {
        $notification->is_viewed = 1;
        $notification->viewed_at = now();
        $notification->save();
        return response()->json(['success' => true], 200);
    }
}
