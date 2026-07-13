<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Modules\Clients\Events;

use FI\Events\Event;
use FI\Modules\Clients\Models\Client;
use Illuminate\Queue\SerializesModels;

class AddTransition extends Event
{
    use SerializesModels;

    public $client;
    public $actionType;
    public $previousValue;
    public $currentValue;

    public function __construct(Client $client, $actionType, $previousValue = null, $currentValue = null)
    {
        $this->client        = $client;
        $this->actionType    = $actionType;
        $this->previousValue = $previousValue;
        $this->currentValue  = $currentValue;

        $this->detail = [
            'id'   => $client->id,
            'name' => $client->name,
            'type' => $client->type,
        ];
    }
}
