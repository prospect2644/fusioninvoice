<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Support\Statuses;

abstract class AbstractStatuses
{
    /**
     * Returns an array of statuses to populate dropdown list.
     *
     * @return array
     */
    public static function lists()
    {
        $statuses = array_combine(static::$statuses, static::$statuses);

        foreach ($statuses as $key => $status)
        {
            $statuses[$key] = trans('fi.' . $status);
        }

        return $statuses;
    }
}