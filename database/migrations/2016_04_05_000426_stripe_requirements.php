<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use FI\Modules\Settings\Models\Setting;
use Illuminate\Database\Migrations\Migration;

class StripeRequirements extends Migration
{
    public function up()
    {
        $merchantConfig = json_decode(Setting::getByKey('merchant'), true);

        $merchantConfig['Stripe']['requireBillingName']    = 0;
        $merchantConfig['Stripe']['requireBillingAddress'] = 0;
        $merchantConfig['Stripe']['requireBillingCity']    = 0;
        $merchantConfig['Stripe']['requireBillingState']   = 0;
        $merchantConfig['Stripe']['requireBillingZip']     = 0;

        Setting::saveByKey('merchant', json_encode($merchantConfig));
    }
}
