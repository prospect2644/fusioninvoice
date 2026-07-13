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

use FI\Modules\Invoices\Events\CreditMemoModified;

class CreditAppliedInvoiceObserver
{
    function created(CreditAppliedInvoice $CreditAppliedInvoice)
    {
        $creditMemo = $CreditAppliedInvoice->creditMemo;
        event(new CreditMemoModified($creditMemo));
        if (auth()->guest() or auth()->user()->user_type == 'client')
        {
            $creditMemo->activities()->create(['activity' => 'public.paid']);
        }
    }

    public function deleted(CreditAppliedInvoice $CreditAppliedInvoice)
    {
        $creditMemo = $CreditAppliedInvoice->creditMemo;
        if ($creditMemo)
        {
            event(new CreditMemoModified($creditMemo));
        }
    }

    public function updated(CreditAppliedInvoice $CreditAppliedInvoice)
    {
        $creditMemo = $CreditAppliedInvoice->creditMemo;
        event(new CreditMemoModified($creditMemo));
    }
}