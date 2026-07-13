<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Modules\CustomFields\Policies;

use FI\Traits\Policy;

class CustomFieldPolicy
{
    use Policy;

    private static $module = 'custom_fields';
}