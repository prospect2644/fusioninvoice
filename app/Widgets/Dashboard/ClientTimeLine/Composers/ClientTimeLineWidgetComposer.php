<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Widgets\Dashboard\ClientTimeline\Composers;

use FI\Modules\Transitions\Models\Transitions;
use FI\Modules\Users\Models\User;

class ClientTimeLineWidgetComposer
{
    public function compose($view)
    {
        $filterUsers = [];
        if (auth()->user()->user_type == 'admin')
        {
            $users = User::select('id', 'name')->get()->toArray();
            foreach ($users as $user)
            {
                $filterUsers[$user['id']] = $user['name'];
            }
        }
        $view->with('modules', Transitions::getModulesList())
            ->with('hideHeader', true)
            ->with('filterUsers', $filterUsers);
    }

}