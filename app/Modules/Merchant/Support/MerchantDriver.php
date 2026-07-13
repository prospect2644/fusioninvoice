<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Modules\Merchant\Support;

abstract class MerchantDriver
{
    public function getName()
    {
        return str_replace('Driver', '', class_basename($this));
    }

    public function getSetting($setting)
    {
        return config('fi.' . $this->getSettingKey($setting));
    }

    public function getSettingKey($setting)
    {
        return 'merchant_' . $this->getName() . '_' . $setting;
    }

    public abstract function getSettings();
}