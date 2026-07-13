<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Modules\Quotes\Models;

use FI\Modules\Quotes\Events\QuoteModified;

class QuoteItemObserver
{
    public function deleted(QuoteItem $quoteItem)
    {
        if ($quoteItem->quote)
        {
            event(new QuoteModified($quoteItem->quote));
        }
    }

    public function deleting(QuoteItem $quoteItem)
    {
        $quoteItem->amount()->delete();
    }

    public function saved(QuoteItem $quoteItem)
    {
        event(new QuoteModified($quoteItem->quote));
    }

    public function saving(QuoteItem $quoteItem)
    {
        $applyExchangeRate = $quoteItem->apply_exchange_rate;
        unset($quoteItem->apply_exchange_rate);

        if ($applyExchangeRate == true)
        {
            $quoteItem->price = $quoteItem->price * $quoteItem->quote->exchange_rate;
        }

        if (!$quoteItem->display_order)
        {
            $displayOrder = QuoteItem::where('quote_id', $quoteItem->quote_id)->max('display_order');

            $displayOrder++;

            $quoteItem->display_order = $displayOrder;
        }
    }
}