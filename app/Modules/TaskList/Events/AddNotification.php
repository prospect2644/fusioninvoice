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

class AddNotification extends Event
{
    use SerializesModels;

    public $task;
    public $detail;

    public function __construct(Task $task, $actionType)
    {
        $this->task       = $task;
        $this->actionType = $actionType;
        $this->detail     = null;
    }
}