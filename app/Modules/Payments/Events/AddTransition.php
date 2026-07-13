<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Modules\Payments\Events;

use FI\Events\Event;
use FI\Modules\Payments\Models\Payment;
use Illuminate\Queue\SerializesModels;

class AddTransition extends Event
{
    use SerializesModels;

    public $payment;
    public $actionType;
    public $previousValue;
    public $currentValue;
    public $detail;

    public function __construct(Payment $payment, $actionType, $previousValue = null, $currentValue = null)
    {
        $this->payment       = $payment;
        $this->actionType    = $actionType;
        $this->previousValue = $previousValue;
        $this->currentValue  = $currentValue;
        $this->detail        = [
            'id'     => $payment->id,
            'amount' => getCurrencySign(config('fi.baseCurrency')) . ' ' . $payment->formatted_numeric_amount,
        ];
    }
}
