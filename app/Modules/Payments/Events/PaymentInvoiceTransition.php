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
use FI\Modules\Payments\Models\PaymentInvoice;
use Illuminate\Queue\SerializesModels;

class PaymentInvoiceTransition extends Event
{
    use SerializesModels;

    public $paymentInvoice;
    public $actionType;
    public $previousValue;
    public $currentValue;
    public $detail;

    public function __construct(PaymentInvoice $paymentInvoice, $actionType, $previousValue = null, $currentValue = null)
    {
        $this->paymentInvoice = $paymentInvoice;
        $this->actionType     = $actionType;
        $this->previousValue  = $previousValue;
        $this->currentValue   = $currentValue;
        $this->detail         = [
            'payment_id'          => $paymentInvoice->payment_id,
            'invoice_number'      => $paymentInvoice->invoice->number,
            'invoice_amount_paid' => $paymentInvoice->formatted_invoice_amount_paid,
            'is_full_amount'      => ((float)$paymentInvoice->invoice->amount->total == (float)$paymentInvoice->invoice_amount_paid) ? 1 : 0,
        ];
    }
}