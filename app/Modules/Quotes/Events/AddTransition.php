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
use FI\Modules\Quotes\Models\Quote;
use Illuminate\Queue\SerializesModels;

class AddTransition extends Event
{
    use SerializesModels;

    public $quote;
    public $actionType;
    public $previousValue;
    public $currentValue;
    public $detail;
    public $userId;

    public function __construct(Quote $quote, $actionType, $previousValue = null, $currentValue = null, $userId = null)
    {
        $this->quote         = $quote;
        $this->actionType    = $actionType;
        $this->previousValue = $previousValue;
        $this->currentValue  = $currentValue;
        $this->userId        = $userId;

        if ($actionType == 'created' || $actionType == 'deleted')
        {
            $this->detail = [
                'number' => $quote->number,
            ];
        }
        if ($actionType == 'updated' || $actionType == 'status_changed' || $actionType == 'email_sent' || $actionType == 'email_opened')
        {
            $this->detail = [
                'number' => $quote->number,
                'amount' => $quote->amount->formatted_total,
            ];
        }
    }
}
