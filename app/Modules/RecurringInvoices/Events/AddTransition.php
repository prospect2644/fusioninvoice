<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Modules\RecurringInvoices\Events;

use FI\Events\Event;
use FI\Modules\RecurringInvoices\Models\RecurringInvoice;
use Illuminate\Queue\SerializesModels;

class AddTransition extends Event
{
    use SerializesModels;

    public $recurringInvoice;
    public $actionType;
    public $previousValue;
    public $currentValue;
    public $detail;
    public $userId;

    public function __construct(RecurringInvoice $recurringInvoice, $actionType, $previousValue = null, $currentValue = null, $userId = null)
    {
        $this->recurringInvoice = $recurringInvoice;
        $this->actionType       = $actionType;
        $this->previousValue    = $previousValue;
        $this->currentValue     = $currentValue;
        $this->userId           = $userId;

        if ($actionType == 'created' || $actionType == 'deleted')
        {
            $this->detail = [
                'number' => $recurringInvoice->id,
            ];
        }

        if ($actionType == 'updated')
        {
            $this->detail = [
                'number' => $recurringInvoice->id,
                'amount' => $recurringInvoice->amount->formatted_total,
            ];
        }

    }
}
