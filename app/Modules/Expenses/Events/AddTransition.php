<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Modules\Expenses\Events;

use FI\Events\Event;
use FI\Modules\Expenses\Models\Expense;
use Illuminate\Queue\SerializesModels;

class AddTransition extends Event
{
    use SerializesModels;

    public $expense;
    public $actionType;
    public $previousValue;
    public $currentValue;
    public $detail;

    public function __construct(Expense $expense, $actionType, $previousValue = null, $currentValue = null)
    {
        $this->expense       = $expense;
        $this->actionType    = $actionType;
        $this->previousValue = $previousValue;
        $this->currentValue  = $currentValue;
        $this->detail        = [
            'amount' => $expense->formatted_amount,
        ];
        if ($actionType == 'billed')
        {
            $this->detail['invoice'] = $expense->invoice->number;
        }
    }
}