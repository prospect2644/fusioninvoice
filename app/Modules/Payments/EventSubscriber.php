<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Modules\Payments;

use FI\Modules\Payments\Events\AddTransition;
use FI\Modules\Payments\Events\PaymentInvoiceTransition;
use FI\Modules\Payments\Events\PaymentEmailed;
use FI\Modules\MailQueue\Support\MailQueue;
use FI\Modules\Transitions\Models\Transitions;
use FI\Support\Contacts;
use FI\Support\Parser;

class EventSubscriber
{
    public function addTransition(AddTransition $event)
    {

        $transition                      = new Transitions();
        $transition->user_id             = $event->payment->user_id;
        $transition->client_id           = $event->payment->client->id;
        $transition->transitionable_id   = $event->payment->id;
        $transition->transitionable_type = 'FI\Modules\Payments\Models\Payment';
        $transition->action_type         = $event->actionType;
        if (!empty($event->detail))
        {
            $transition->detail = json_encode($event->detail);
        }
        $transition->previous_value = $event->previousValue;
        $transition->current_value  = $event->currentValue;
        $transition->save();

    }

    public function paymentInvoiceTransition(PaymentInvoiceTransition $event)
    {

        $transition                      = new Transitions();
        $transition->user_id             = $event->paymentInvoice->payment->user_id;
        $transition->client_id           = $event->paymentInvoice->invoice->client_id;
        $transition->transitionable_id   = $event->paymentInvoice->id;
        $transition->transitionable_type = 'FI\Modules\Payments\Models\PaymentInvoice';
        $transition->action_type         = $event->actionType;
        if (!empty($event->detail))
        {
            $transition->detail = json_encode($event->detail);
        }
        $transition->previous_value = $event->previousValue;
        $transition->current_value  = $event->currentValue;
        $transition->save();

    }

    public function paymentEmailed(PaymentEmailed $event)
    {
        // Change the status to sent if the status is currently draft
        $payment = $event->payment;
        $parser  = new Parser($payment);

        $contacts = new Contacts($payment->client);

        $mailQueue = new MailQueue();

        $mail = $mailQueue->create($payment, [
            'to'             => $contacts->getSelectedContactsTo(),
            'cc'             => $contacts->getSelectedContactsCc(),
            'bcc'            => $contacts->getSelectedContactsBcc(),
            'subject'        => $parser->parse('paymentReceiptEmailSubject'),
            'body'           => (config('fi.paymentReceiptBody') == 'default') ? $parser->parse('paymentReceiptBody', 'default') : $parser->parse('paymentReceiptBody', 'custom'),
            'attach_pdf'     => 0,
            'attach_invoice' => config('fi.paymentAttachInvoice') ? config('fi.paymentAttachInvoice') : 0,
        ]);

        $mailQueue->send($mail->id);
        $transition                      = new Transitions();
        $transition->user_id             = auth()->user()->id;
        $transition->client_id           = $payment->client_id;
        $transition->transitionable_id   = $payment->id;
        $transition->transitionable_type = 'FI\Modules\Payments\Models\Payment';
        $transition->action_type         = $event->actionType;
        if (!empty($event->detail))
        {
            $transition->detail = json_encode($event->detail);
        }
        $transition->previous_value = $event->previousValue;
        $transition->current_value  = $event->currentValue;
        $transition->save();
    }

    public function subscribe($events)
    {
        $events->listen('FI\Modules\Payments\Events\PaymentEmailed', 'FI\Modules\Payments\EventSubscriber@paymentEmailed');
        $events->listen('FI\Modules\Payments\Events\AddTransition', 'FI\Modules\Payments\EventSubscriber@addTransition');
        $events->listen('FI\Modules\Payments\Events\PaymentInvoiceTransition', 'FI\Modules\Payments\EventSubscriber@paymentInvoiceTransition');
    }
}
