<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Modules\Invoices\Support;

use FI\Modules\Invoices\Models\InvoiceAmount;
use FI\Modules\Payments\Models\Payment;

class CreditApplied
{
    public function adjust($creditMemo)
    {
        $totalApplied = Payment::where('credit_memo_id', $creditMemo->id)->sum('amount');

        $creditMemoAmount = InvoiceAmount::where('invoice_id', $creditMemo->id)->first();
        if ($creditMemoAmount)
        {
            $creditMemoCurrentTotal = abs($creditMemoAmount->total);

            if ($totalApplied < $creditMemoCurrentTotal)
            {
                $creditMemoUpdatedTotal = (-1 * ($creditMemoCurrentTotal - $totalApplied));
            }
            elseif ($totalApplied == $creditMemoCurrentTotal)
            {
                $creditMemoUpdatedTotal = 0;
            }
            else
            {
                //this case ideally should not execute
                $creditMemoUpdatedTotal = (-1 * ($creditMemoCurrentTotal - $totalApplied));
            }
            $creditMemoAmount->balance = $creditMemoUpdatedTotal;
            $creditMemoAmount->paid    = (-1 * $totalApplied);
            $creditMemoAmount->save();

            if ($creditMemoAmount->balance == 0 and $creditMemo->status_text != 'canceled')
            {
                $creditMemo->status = 'applied';
                $creditMemo->save();
            }
            else
            {
                $creditMemo->status = 'draft';
                $creditMemo->save();
            }
        }
    }
}