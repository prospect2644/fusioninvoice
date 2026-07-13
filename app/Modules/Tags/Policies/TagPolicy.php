<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Modules\Tags\Policies;

use FI\Traits\Policy;

class TagPolicy
{
    use Policy;

    private static $module = 'tags';
}