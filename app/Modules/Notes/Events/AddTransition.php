<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Modules\Notes\Events;

use FI\Events\Event;
use FI\Modules\Notes\Models\Note;
use Illuminate\Queue\SerializesModels;

class AddTransition extends Event
{
    use SerializesModels;

    public $note;
    public $actionType;
    public $previousValue;
    public $currentValue;
    public $detail;

    public function __construct(Note $note, $actionType, $previousValue = null, $currentValue = null)
    {
        $this->note          = $note;
        $this->actionType    = $actionType;
        $this->previousValue = $previousValue;
        $this->currentValue  = $currentValue;
        $this->detail        = [
            'short_text' => $note->note,
        ];
    }
}