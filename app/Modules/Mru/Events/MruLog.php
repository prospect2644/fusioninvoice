<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Modules\Mru\Events;

use FI\Events\Event;
use Illuminate\Queue\SerializesModels;

class MruLog extends Event
{
    use SerializesModels;

    public $mruData;

    public function __construct($mruData)
    {
        $this->mruData = $mruData;
    }
}
