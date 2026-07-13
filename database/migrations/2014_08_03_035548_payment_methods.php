<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use FI\Modules\PaymentMethods\Models\PaymentMethod;
use Illuminate\Database\Migrations\Migration;

class PaymentMethods extends Migration
{
    public function up()
    {
        // If this is a new install, no payment methods will exist, so let's
        // create some.

        if (PaymentMethod::count() == 0)
        {
            PaymentMethod::create(['name' => trans('fi.cash')]);
            PaymentMethod::create(['name' => trans('fi.credit_card')]);
            PaymentMethod::create(['name' => trans('fi.online_payment')]);
        }
    }
}
