<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Modules\Quotes;

use Carbon\Carbon;
use FI\Modules\MailQueue\Support\MailQueue;
use FI\Modules\Quotes\Events\AddTransition;
use FI\Modules\Quotes\Events\QuoteApproved;
use FI\Modules\Quotes\Events\QuoteEmailed;
use FI\Modules\Quotes\Events\QuoteEmailing;
use FI\Modules\Quotes\Events\QuoteModified;
use FI\Modules\Quotes\Events\QuoteRejected;
use FI\Modules\Quotes\Events\QuoteToInvoiceTransition;
use FI\Modules\Quotes\Events\QuoteViewed;
use FI\Modules\Quotes\Support\QuoteCalculate;
use FI\Modules\Quotes\Support\QuoteToInvoice;
use FI\Modules\Transitions\Models\Transitions;
use FI\Modules\Users\Models\User;
use FI\Support\DateFormatter;
use FI\Support\Parser;

class EventSubscriber
{
    public function quoteApproved(QuoteApproved $event)
    {
        // Create the activity record
        $event->quote->activities()->create(['activity' => 'public.approved']);

        // If applicable, convert the quote to an invoice when quote is approved
        if (config('fi.convertQuoteWhenApproved'))
        {
            $quoteToInvoice = new QuoteToInvoice();

            $invoice = $quoteToInvoice->convert(
                $event->quote,
                date('Y-m-d'),
                DateFormatter::incrementDateByDays(date('Y-m-d'), config('fi.invoicesDueAfter')),
                config('fi.invoiceGroup')
            );

            $userId = User::whereUserType('system')->first()->id;

            event(new QuoteToInvoiceTransition($event->quote, $invoice, $userId));
        }

        $parser = new Parser($event->quote);

        $mailQueue = new MailQueue();

        $mail = $mailQueue->create($event->quote, [
            'to'         => [$event->quote->user->email],
            'cc'         => [config('fi.mailDefaultCc')],
            'bcc'        => [config('fi.mailDefaultBcc')],
            'subject'    => trans('fi.quote_status_change_notification'),
            'body'       => $parser->parse('quoteApprovedEmailBody'),
            'attach_pdf' => config('fi.attachPdf'),
        ]);

        $mailQueue->send($mail->id);
    }

    public function quoteEmailed(QuoteEmailed $event)
    {
        // Change the status to sent if the status is currently draft
        if ($event->quote->status == 'draft')
        {
            $event->quote->status = 'sent';
            $event->quote->save();
        }
    }

    public function quoteEmailing(QuoteEmailing $event)
    {
        if (config('fi.resetQuoteDateEmailDraft') and $event->quote->status_text == 'draft')
        {
            $event->quote->quote_date = date('Y-m-d');
            $event->quote->expires_at = DateFormatter::incrementDateByDays(date('Y-m-d'), config('fi.quotesExpireAfter'));
            $event->quote->save();
        }
    }

    public function quoteModified(QuoteModified $event)
    {
        $quoteCalculate = new QuoteCalculate();

        $quoteCalculate->calculate($event->quote);
    }

    public function quoteRejected(QuoteRejected $event)
    {
        $event->quote->activities()->create(['activity' => 'public.rejected']);

        $parser = new Parser($event->quote);

        $mailQueue = new MailQueue();

        $mail = $mailQueue->create($event->quote, [
            'to'         => [$event->quote->user->email],
            'cc'         => [config('fi.mailDefaultCc')],
            'bcc'        => [config('fi.mailDefaultBcc')],
            'subject'    => trans('fi.quote_status_change_notification'),
            'body'       => $parser->parse('quoteRejectedEmailBody'),
            'attach_pdf' => config('fi.attachPdf'),
        ]);

        $mailQueue->send($mail->id);
    }

    public function quoteViewed(QuoteViewed $event)
    {
        if (request('disableFlag') != 1)
        {
            if (auth()->guest() or auth()->user()->user_type == 'client')
            {
                $event->quote->activities()->create(['activity' => 'public.viewed']);
                $event->quote->viewed = 1;
                $event->quote->save();
            }
        }
    }

    public function addTransition(AddTransition $event)
    {
        $userId             = isset(auth()->user()->id) ? auth()->user()->id : $event->userId;
        $transitionableType = 'FI\Modules\Quotes\Models\Quote';

        $transitions = Transitions::whereUserId($userId)
            ->whereClientId($event->quote->client->id)
            ->whereTransitionableId($event->quote->id)
            ->whereTransitionableType($transitionableType)
            ->whereActionType($event->actionType)
            ->whereDate('created_at', Carbon::today())->orderBy('id', 'DESC')->get();

        if ($transitions->count() > 0)
        {
            if (!empty($event->detail))
            {
                $detail                        = json_decode($transitions[0]->detail);
                $action_count                  = isset($detail->action_count) ? $detail->action_count + 1 : $transitions->count() + 1;
                $event->detail['action_count'] = $action_count;
                Transitions::whereId($transitions[0]->id)->update(['detail' => json_encode($event->detail)]);
            }
        }
        else
        {
            $transition                      = new Transitions();
            $transition->user_id             = $userId;
            $transition->client_id           = $event->quote->client->id;
            $transition->transitionable_id   = $event->quote->id;
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

    public function quoteToInvoiceTransition(QuoteToInvoiceTransition $event)
    {
        $userId = isset(auth()->user()->id) ? auth()->user()->id : $event->userId;

        $transition                      = new Transitions();
        $transition->user_id             = $userId;
        $transition->client_id           = $event->quote->client->id;
        $transition->transitionable_id   = $event->quote->id;
        $transition->transitionable_type = 'FI\Modules\Quotes\Models\Quote';
        $transition->action_type         = $event->actionType;
        if (!empty($event->detail))
        {
            $transition->detail = json_encode($event->detail);
        }
        $transition->save();

    }

    public function subscribe($events)
    {
        $events->listen('FI\Modules\Quotes\Events\AddTransition', 'FI\Modules\Quotes\EventSubscriber@addTransition');
        $events->listen('FI\Modules\Quotes\Events\QuoteApproved', 'FI\Modules\Quotes\EventSubscriber@quoteApproved');
        $events->listen('FI\Modules\Quotes\Events\QuoteEmailed', 'FI\Modules\Quotes\EventSubscriber@quoteEmailed');
        $events->listen('FI\Modules\Quotes\Events\QuoteEmailing', 'FI\Modules\Quotes\EventSubscriber@quoteEmailing');
        $events->listen('FI\Modules\Quotes\Events\QuoteModified', 'FI\Modules\Quotes\EventSubscriber@quoteModified');
        $events->listen('FI\Modules\Quotes\Events\QuoteRejected', 'FI\Modules\Quotes\EventSubscriber@quoteRejected');
        $events->listen('FI\Modules\Quotes\Events\QuoteToInvoiceTransition', 'FI\Modules\Quotes\EventSubscriber@quoteToInvoiceTransition');
        $events->listen('FI\Modules\Quotes\Events\QuoteViewed', 'FI\Modules\Quotes\EventSubscriber@quoteViewed');
    }
}
