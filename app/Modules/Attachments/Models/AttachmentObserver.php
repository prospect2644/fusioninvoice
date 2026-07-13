<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Modules\Attachments\Models;

use Illuminate\Support\Str;

class AttachmentObserver
{
    public function creating(Attachment $attachment)
    {
        $attachment->url_key = Str::random(64);
    }
}