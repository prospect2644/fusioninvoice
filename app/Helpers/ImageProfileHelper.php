<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use FI\Support\ProfileImage\ProfileImageFactory;

function profileImageUrl($user)
{
    $profileImage = ProfileImageFactory::create();

    return $profileImage->getProfileImageUrl($user);
}