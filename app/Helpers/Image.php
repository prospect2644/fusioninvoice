<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

function image($imagePath, $width, $height)
{
    $image = base64_encode(file_get_contents($imagePath));

    return '<img src="data:image/png;base64,' . $image . '" style="width: ' . $width . 'px; height: ' . $height . 'px;">';
}