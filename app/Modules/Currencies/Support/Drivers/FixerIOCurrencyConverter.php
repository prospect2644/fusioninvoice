<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Modules\Currencies\Support\Drivers;

use Exception;

class FixerIOCurrencyConverter
{
    /**
     * Returns the currency conversion rate.
     *
     * @param string $from
     * @param string $to
     * @return string
     */
    public function convert($from, $to)
    {
        try
        {
            $result = json_decode(file_get_contents('https://api.exchangeratesapi.io/latest?base=' . $from . '&symbols=' . $to), true);

            return number_format($result['rates'][strtoupper($to)], 7);
        }
        catch (Exception $e)
        {
            return '1.0000000';
        }

    }
}