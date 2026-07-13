<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

function deleteViewCache()
{
    foreach (File::files(storage_path('framework/views')) as $file)
    {
        try
        {
            unlink($file);
        }
        catch (Exception $e)
        {
            Log::info('Could not delete ' . $file);
        }
    }
}

function deleteTempFiles()
{
    foreach (File::files(storage_path()) as $file)
    {
        if (in_array(File::extension($file), ['pdf', 'csv']))
        {
            try
            {
                unlink($file);
            }
            catch (Exception $e)
            {
                Log::info('Could not delete ' . $file);
            }
        }
    }
}