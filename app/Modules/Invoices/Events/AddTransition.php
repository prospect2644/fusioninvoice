<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Modules\Invoices\Events;

use FI\Events\Event;
use FI\Modules\Invoices\Models\Invoice;
use Illuminate\Queue\SerializesModels;

class AddTransition extends Event
{
    use SerializesModels;

    public $invoice;
    public $actionType;
    public $previousValue;
    public $currentValue;
    public $detail;
    public $userId;

    public function __construct(Invoice $invoice, $actionType, $previousValue = null, $currentValue = null, $userId = null)
    {
        $this->invoice       = $invoice;
        $this->actionType    = $actionType;
        $this->previousValue = $previousValue;
        $this->currentValue  = $currentValue;
        $this->userId        = $userId;

        if ($actionType == 'credit_memo_created' || $actionType == 'created' || $actionType == 'deleted' || $actionType == 'credit_memo_deleted')
        {
            $this->detail = [
                'number' => $invoice->number,
            ];
        }

        if ($actionType == 'created_from_recurring')
        {
            $this->detail = [
                'number'               => $invoice->number,
                'recurring_invoice_id' => $invoice->recurring_invoice_id,
            ];
        }
        if ($actionType == 'updated' || $actionType == 'credit_memo_updated' || $actionType == 'status_changed' || $actionType == 'email_sent' || $actionType == 'email_opened')
        {
            $this->detail = [
                'number' => $invoice->number,
                'amount' => $invoice->amount->formatted_balance,
            ];
        }

    }
}
