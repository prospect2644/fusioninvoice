<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Modules\RecurringInvoices\Models;

use FI\Modules\RecurringInvoices\Events\RecurringInvoiceModified;

class RecurringInvoiceItemObserver
{
    public function deleted(RecurringInvoiceItem $recurringInvoiceItem)
    {
        if ($recurringInvoiceItem->recurringInvoice)
        {
            event(new RecurringInvoiceModified($recurringInvoiceItem->recurringInvoice));
        }
    }

    public function deleting(RecurringInvoiceItem $recurringInvoiceItem)
    {
        $recurringInvoiceItem->amount()->delete();
    }

    public function saved(RecurringInvoiceItem $recurringInvoiceItem)
    {
        event(new RecurringInvoiceModified($recurringInvoiceItem->recurringInvoice));
    }

    public function saving(RecurringInvoiceItem $recurringInvoiceItem)
    {
        $applyExchangeRate = $recurringInvoiceItem->apply_exchange_rate;
        unset($recurringInvoiceItem->apply_exchange_rate);

        if ($applyExchangeRate == true)
        {
            $recurringInvoiceItem->price = $recurringInvoiceItem->price * $recurringInvoiceItem->invoice->exchange_rate;
        }

        if (!$recurringInvoiceItem->display_order)
        {
            $displayOrder = RecurringInvoiceItem::where('invoice_id', $recurringInvoiceItem->recurring_invoice_id)->max('display_order');

            $displayOrder++;

            $recurringInvoiceItem->display_order = $displayOrder;
        }
    }
}