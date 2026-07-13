<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

function addon_path($path = '')
{
    return base_path('custom/addons') . ($path ? DIRECTORY_SEPARATOR . $path : $path);
}

function urlSegments($url)
{
    return explode('/', $url);
}

function media_path($path = '')
{
    return config('filesystems.media.root') . ($path ? DIRECTORY_SEPARATOR . $path : $path);
}

function company_profile_logo_path($path = '')
{
    return media_path('company_profile') . ($path ? DIRECTORY_SEPARATOR . $path : $path);
}

function custom_field_path($path = '')
{
    return media_path('custom_fields') . ($path ? DIRECTORY_SEPARATOR . $path : $path);
}
