<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Modules\Reports\Policies;

use FI\Traits\Policy;

class TaxSummaryPolicy
{
    use Policy;

    private static $module = 'tax_summary';
}