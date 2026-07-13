<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

function periods()
{
    return [
        'all_time'          => trans('fi.all_time'),
        'this_month'        => trans('fi.this_month'),
        'this_quarter'      => trans('fi.this_quarter'),
        'year_to_date'      => trans('fi.this_year'),
        'last_month'        => trans('fi.last_month'),
        'last_quarter'      => trans('fi.last_quarter'),
        'last_year'         => trans('fi.last_year'),
        'custom_date_range' => trans('fi.custom_date_range'),
    ];
}