<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Modules\TaskList\Events;

use FI\Events\Event;
use FI\Modules\TaskList\Models\Task;
use Illuminate\Queue\SerializesModels;

class AddTransition extends Event
{
    use SerializesModels;

    public $task;
    public $actionType;
    public $previousValue;
    public $currentValue;
    public $detail;

    public function __construct(Task $task, $actionType, $previousValue = null, $currentValue = null)
    {
        $this->task          = $task;
        $this->actionType    = $actionType;
        $this->previousValue = $previousValue;
        $this->currentValue  = $currentValue;
        $this->detail        = [
            'short_title' => $task->formatted_short_title,
            'short_text'  => $task->description
        ];
    }
}