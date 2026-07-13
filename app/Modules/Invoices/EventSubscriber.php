<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Modules\Invoices;

use Carbon\Carbon;
use FI\Modules\Invoices\Events\AddTransition;
use FI\Modules\Invoices\Events\CreditMemoModified;
use FI\Modules\Invoices\Events\InvoiceCreatedRecurring;
use FI\Modules\Invoices\Events\InvoiceEmailed;
use FI\Modules\Invoices\Events\InvoiceEmailing;
use FI\Modules\Invoices\Events\InvoiceModified;
use FI\Modules\Invoices\Events\InvoiceViewed;
use FI\Modules\Invoices\Support\CreditApplied;
use FI\Modules\Invoices\Support\InvoiceCalculate;
use FI\Modules\MailQueue\Support\MailQueue;
use FI\Modules\Transitions\Models\Transitions;
use FI\Modules\Users\Models\User;
use FI\Support\Contacts;
use FI\Support\Parser;

class EventSubscriber
{
    public function invoiceCreatedRecurring(InvoiceCreatedRecurring $event)
    {
        if ($event->invoice->client->getAutomaticEmailOnRecur())
        {
            $parser = new Parser($event->invoice);

            $contacts             = new Contacts($event->invoice->client);
            $overdueAttachInvoice = 0;

            if (!$event->invoice->is_overdue)
            {
                $subject = $parser->parse('invoiceEmailSubject');
                $body    = $parser->parse('invoiceEmailBody');
            }
            else
            {
                $overdueAttachInvoice = config('fi.overdueAttachInvoice') ? config('fi.overdueAttachInvoice') : 0;
                $subject              = $parser->parse('overdueInvoiceEmailSubject');
                $body                 = $parser->parse('overdueInvoiceEmailBody');
            }

            $contactTo = $contacts->getSelectedContactsTo();

            $mailQueue = new MailQueue();

            $mail = $mailQueue->create($event->invoice, [
                'to'             => $contactTo,
                'cc'             => $contacts->getSelectedContactsCc(),
                'bcc'            => $contacts->getSelectedContactsBcc(),
                'subject'        => $subject,
                'body'           => $body,
                'attach_pdf'     => config('fi.attachPdf'),
                'attach_invoice' => $overdueAttachInvoice,
            ]);

            $mailQueue->send($mail->id);

            event(new InvoiceEmailed($event->invoice));
        }
    }

    public function invoiceEmailed(InvoiceEmailed $event)
    {
        // Change the status to sent if the status is currently draft
        if ($event->invoice->status == 'draft')
        {
            $event->invoice->status = 'sent';
            $event->invoice->save();
        }
    }

    public function invoiceEmailing(InvoiceEmailing $event)
    {
        // Leaving stub event
    }

    public function invoiceModified(InvoiceModified $event)
    {
        $invoiceCalculate = new InvoiceCalculate();

        $invoiceCalculate->calculate($event->invoice);
    }

    public function creditMemoModified(CreditMemoModified $event)
    {
        $creditApplied = new CreditApplied();

        $creditApplied->adjust($event->creditMemo);
    }

    public function invoiceViewed(InvoiceViewed $event)
    {
        if (request('disableFlag') != 1)
        {
            if (auth()->guest() or auth()->user()->user_type == 'client')
            {
                $event->invoice->activities()->create(['activity' => 'public.viewed']);
                $event->invoice->viewed = 1;
                $event->invoice->save();
            }
        }
    }

    public function addTransition(AddTransition $event)
    {
        $userId = isset(auth()->user()->id) ? auth()->user()->id : $event->userId;

        if ($userId == null)
        {
            $userId = User::whereUserType('system')->first()->id;
        }

        $transitionableType = 'FI\Modules\Invoices\Models\Invoice';

        $invoiceCreated = Carbon::parse($event->invoice->created_at);
        if ($invoiceCreated->diffInMinutes(Carbon::now()) <= 60 && $event->actionType == 'updated')
        {
            $event->actionType = 'created';
        }

        $transitions = Transitions::whereUserId($userId)
            ->whereClientId($event->invoice->client->id)
            ->whereTransitionableId($event->invoice->id)
            ->whereTransitionableType($transitionableType)
            ->whereActionType($event->actionType)
            ->whereDate('created_at', Carbon::today())->orderBy('id', 'DESC')->get();

        if ($transitions->count() > 0)
        {
            if (!empty($event->detail))
            {
                if ($event->actionType != 'created')
                {
                    $detail                        = json_decode($transitions[0]->detail);
                    $action_count                  = isset($detail->action_count) ? $detail->action_count + 1 : $transitions->count() + 1;
                    $event->detail['action_count'] = $action_count;
                }
                Transitions::whereId($transitions[0]->id)->update(['detail' => json_encode($event->detail)]);
            }
        }
        else
        {
            $transition                      = new Transitions();
            $transition->user_id             = $userId;
            $transition->client_id           = $event->invoice->client->id;
            $transition->transitionable_id   = $event->invoice->id;
            $transition->transitionable_type = $transitionableType;
            $transition->action_type         = $event->actionType;
            if (!empty($event->detail))
            {
                $transition->detail = json_encode($event->detail);
            }
            $transition->previous_value = $event->previousValue;
            $transition->current_value  = $event->currentValue;
            $transition->save();
        }

    }

    public function subscribe($events)
    {

        $events->listen('FI\Modules\Invoices\Events\AddTransition', 'FI\Modules\Invoices\EventSubscriber@addTransition');
        $events->listen('FI\Modules\Invoices\Events\InvoiceCreatedRecurring', 'FI\Modules\Invoices\EventSubscriber@invoiceCreatedRecurring');
        $events->listen('FI\Modules\Invoices\Events\InvoiceEmailed', 'FI\Modules\Invoices\EventSubscriber@invoiceEmailed');
        $events->listen('FI\Modules\Invoices\Events\InvoiceEmailing', 'FI\Modules\Invoices\EventSubscriber@invoiceEmailing');
        $events->listen('FI\Modules\Invoices\Events\InvoiceModified', 'FI\Modules\Invoices\EventSubscriber@invoiceModified');
        $events->listen('FI\Modules\Invoices\Events\CreditMemoModified', 'FI\Modules\Invoices\EventSubscriber@creditMemoModified');
        $events->listen('FI\Modules\Invoices\Events\InvoiceViewed', 'FI\Modules\Invoices\EventSubscriber@invoiceViewed');
    }
}
