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
use FI\Modules\MailQueue\Models\MailQueue;
use Illuminate\Queue\SerializesModels;

class OverdueNoticeEmailed extends Event
{
    use SerializesModels;

    public $invoice;
    public $mail;

    public function __construct(Invoice $invoice, MailQueue $mail)
    {
        $this->invoice = $invoice;
        $this->mail    = $mail;
    }
}
