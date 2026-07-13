<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Modules\PaymentMethods\Policies;

use FI\Traits\Policy;

class PaymentMethodPolicy
{
    use Policy;

    private static $module = 'payment_methods';
}