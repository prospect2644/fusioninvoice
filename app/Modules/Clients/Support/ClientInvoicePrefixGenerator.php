<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Modules\Clients\Support;

use FI\Modules\Clients\Models\Client;

class ClientInvoicePrefixGenerator
{
    public function invoicePrefixGenerator()
    {
        $permittedChars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';

        return substr(str_shuffle($permittedChars), 0, 5);
    }

    public function isUnique($invoicePrefix)
    {
        $client = Client::whereInvoicePrefix($invoicePrefix)->first();

        return $client ? true : false;
    }
}