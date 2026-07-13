<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Modules\Invoices\Models;

use FI\Modules\Invoices\Events\InvoiceModified;

class InvoiceItemObserver
{
    public function deleted(InvoiceItem $invoiceItem)
    {
        if ($invoiceItem->invoice)
        {
            event(new InvoiceModified($invoiceItem->invoice));
        }
    }

    public function deleting(InvoiceItem $invoiceItem)
    {
        $invoiceItem->amount()->delete();
    }

    public function saved(InvoiceItem $invoiceItem)
    {
        event(new InvoiceModified($invoiceItem->invoice));
    }

    public function saving(InvoiceItem $invoiceItem)
    {
        $applyExchangeRate = $invoiceItem->apply_exchange_rate;
        unset($invoiceItem->apply_exchange_rate);

        if ($applyExchangeRate == true)
        {
            $invoiceItem->price = $invoiceItem->price * $invoiceItem->invoice->exchange_rate;
        }

        if (!$invoiceItem->display_order)
        {
            $displayOrder = InvoiceItem::where('invoice_id', $invoiceItem->invoice_id)->max('display_order');

            $displayOrder++;

            $invoiceItem->display_order = $displayOrder;
        }

        if (is_null($invoiceItem->tax_rate_id))
        {
            $invoiceItem->tax_rate_id = 0;
        }

        if (is_null($invoiceItem->tax_rate_2_id))
        {
            $invoiceItem->tax_rate_2_id = 0;
        }
    }
}