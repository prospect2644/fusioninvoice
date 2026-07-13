<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Modules\Settings\Controllers;

use Exception;
use FI\Http\Controllers\Controller;
use Ifsnop\Mysqldump\Mysqldump;
use Illuminate\Support\Facades\Log;

class BackupController extends Controller
{
    public function database()
    {
        $default  = config('database.default');
        $host     = config('database.connections.' . $default . '.host');
        $dbname   = config('database.connections.' . $default . '.database');
        $port   = config('database.connections.' . $default . '.port');
        $username = config('database.connections.' . $default . '.username');
        $password = config('database.connections.' . $default . '.password');
        $filename = storage_path('FusionInvoice_' . date('Y-m-d_H-i-s') . '.sql');

        try
        {
            $dump = new Mysqldump('mysql:host=' . $host . ';port='.$port . ';dbname=' . $dbname, $username, $password);
            $dump->start($filename);
        }
        catch (Exception $e)
        {
            Log::error($e->getMessage());
        }

        return response()->download($filename)->deleteFileAfterSend(true);
    }
}