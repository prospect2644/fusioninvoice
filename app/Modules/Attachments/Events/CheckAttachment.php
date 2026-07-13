<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Modules\Attachments\Events;

use FI\Events\Event;
use Illuminate\Queue\SerializesModels;

class CheckAttachment extends Event
{
    use SerializesModels;

    public function __construct($object)
    {
        $this->object = $object;
    }

}
