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

use FI\Modules\Mru\Models\Mru;

class MruComposer
{
    public function compose($view)
    {
        $moduleIconMapping = [
            'clients'            => 'fa-users',
            'quotes'             => 'fa-file-text-o',
            'invoices'           => 'fa-file-text',
            'recurring_invoices' => 'fa-refresh',
            'payments'           => 'fa-credit-card',
            'expenses'           => 'fa-bank',
            'reports'            => 'fa-bar-chart-o',
            'time_tracking'      => 'fa-clock-o',
            'containers'         => 'fa-truck',
        ];

        $mruList = Mru::whereUserId(auth()->user()->id)->limit(10)->orderBy('updated_at', 'DESC')->get();

        $view->with('mruList', $mruList)->with('moduleIconMapping', $moduleIconMapping);
    }
}