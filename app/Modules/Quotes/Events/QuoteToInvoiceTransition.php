<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Modules\Quotes\Events;

use FI\Events\Event;
use FI\Modules\Invoices\Models\Invoice;
use FI\Modules\Quotes\Models\Quote;
use Illuminate\Queue\SerializesModels;

class QuoteToInvoiceTransition extends Event
{
    use SerializesModels;

    public $actionType;
    public $invoice;
    public $quote;
    public $userId;

    public function __construct(Quote $quote, Invoice $invoice, $userId = null)
    {
        $this->actionType = 'quote_to_invoice';
        $this->invoice    = $invoice;
        $this->quote      = $quote;
        $this->detail     = [
            'quote_number'   => $quote->number,
            'invoice_number' => $invoice->number,
        ];
        $this->userId     = $userId;
    }
}
