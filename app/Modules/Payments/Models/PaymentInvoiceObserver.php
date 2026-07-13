<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Modules\Payments\Models;

use FI\Modules\Invoices\Events\CreditMemoModified;
use FI\Modules\Invoices\Events\InvoiceModified;
use FI\Modules\Payments\Events\PaymentInvoiceTransition;

class PaymentInvoiceObserver
{
    function created(PaymentInvoice $paymentInvoice)
    {
        $payment = $paymentInvoice->payment;
        $invoice = $paymentInvoice->invoice;
        event(new InvoiceModified($invoice));
        if (auth()->guest() or auth()->user()->user_type == 'client')
        {
            $invoice->activities()->create(['activity' => 'public.paid']);
        }
        if ($payment->credit_memo_id)
        {
            event(new CreditMemoModified($payment->creditMemo));
        }

        event(new PaymentInvoiceTransition($paymentInvoice, 'payment_received'));
    }

    public function deleted(PaymentInvoice $paymentInvoice)
    {
        $invoice = $paymentInvoice->invoice()->first();
        if ($invoice)
        {
            event(new InvoiceModified($invoice));
            event(new PaymentInvoiceTransition($paymentInvoice, 'payment_reversed'));
        }

        $payment = $paymentInvoice->payment;

        if ($payment->type != 'credit-memo')
        {
            $payment->remaining_balance = $payment->remaining_balance + $paymentInvoice->invoice_amount_paid;
            $payment->type              = 'pre-payment';
            $payment->save();
        }
        else
        {
            event(new CreditMemoModified($payment->creditMemo));
            event(new InvoiceModified($payment->creditMemo));
        }

        if ($payment->paymentInvoice->count() == 0)
        {
            if ($payment->type == 'credit-memo')
            {
                $payment->delete();
            }
            elseif ($payment->type == 'single')
            {
                $payment->delete();
            }
        }

    }

    public function updated(PaymentInvoice $paymentInvoice)
    {
        $invoice = $paymentInvoice->invoice()->first();
        event(new InvoiceModified($invoice));
        event(new PaymentInvoiceTransition($paymentInvoice, 'payment_updated'));

        $payment = $paymentInvoice->payment;
        if ($payment->credit_memo_id)
        {
            event(new CreditMemoModified($payment->creditMemo));
        }
    }
}
