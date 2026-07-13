<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Modules\SystemLog\Controllers;

class SystemLogController
{
    public function index()
    {
        return view('system_log.index')
            ->with(['logs' => logReader(storage_path('logs/laravel.log'))]);
    }
}