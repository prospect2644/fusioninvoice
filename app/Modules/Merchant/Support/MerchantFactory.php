<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Modules\Merchant\Support;

use FI\Support\Directory;

class MerchantFactory
{
    /**
     * @return MerchantDriver
     */
    public static function create($driver)
    {
        $driver = 'FI\\Modules\\Merchant\\Support\\Drivers\\' . $driver;

        return new $driver;
    }

    public static function getDrivers($enabledOnly = false)
    {
        $files = Directory::listContents(app_path('Modules/Merchant/Support/Drivers'));

        $drivers = [];

        foreach ($files as $file)
        {
            $file = basename($file, '.php');

            $driver = self::create($file);

            if (!$enabledOnly or $enabledOnly and $driver->getSetting('enabled'))
            {
                $drivers[$file] = $driver;
            }
        }

        return $drivers;
    }
}