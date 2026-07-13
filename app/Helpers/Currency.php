<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

function getCurrencyClass($currency)
{
    if ($currency == 'AUD')
    {
        return 'fa-usd';
    }
    elseif ($currency == 'CAD')
    {
        return 'fa-usd';
    }
    elseif ($currency == 'EUR')
    {
        return 'fa-eur';
    }
    elseif ($currency == 'GBP')
    {
        return 'fa-gbp';
    }
    elseif ($currency == 'USD')
    {
        return 'fa-usd';
    }

}

function getCurrencySign($currency)
{
    if (in_array($currency, ['AUD', 'CAD', 'USD']))
    {
        return '$';
    }
    elseif ($currency == 'EUR')
    {
        return '€';
    }
    elseif ($currency == 'GBP')
    {
        return '£';
    }

}
