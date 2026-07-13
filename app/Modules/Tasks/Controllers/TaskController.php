<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Modules\Tasks\Controllers;

use Carbon\Carbon;
use FI\Http\Controllers\Controller;
use FI\Jobs\GenerateTimelineHistory;
use FI\Modules\CustomFields\Models\CustomField;
use FI\Modules\Invoices\Events\InvoiceCreatedRecurring;
use FI\Modules\Invoices\Events\OverdueNoticeEmailed;
use FI\Modules\Invoices\Models\Invoice;
use FI\Modules\Invoices\Models\InvoiceItem;
use FI\Modules\MailQueue\Support\MailQueue;
use FI\Modules\RecurringInvoices\Models\RecurringInvoice;
use FI\Modules\Tags\Models\Tag;
use FI\Modules\Users\Models\User;
use FI\Support\Contacts;
use FI\Support\DateFormatter;
use FI\Support\Parser;
use Illuminate\Support\Facades\Log;

class TaskController extends Controller
{
    private $mailQueue;

    public function __construct(MailQueue $mailQueue)
    {
        $this->mailQueue = $mailQueue;
    }

    public function run()
    {
        $this->recurInvoices();

        $this->queueOverdueInvoices();

        $this->queueUpcomingInvoices();
    }

    private function queueUpcomingInvoices()
    {
        $days = config('fi.upcomingPaymentNoticeFrequency');

        if ($days)
        {
            $days = explode(',', $days);

            foreach ($days as $daysFromNow)
            {
                $daysFromNow = trim($daysFromNow);

                if (is_numeric($daysFromNow))
                {
                    $daysFromNow = intval($daysFromNow);

                    $date = Carbon::now()->addDays($daysFromNow)->format('Y-m-d');

                    $invoices = Invoice::with('client')
                        ->where('status', '=', 'sent')
                        ->whereHas('amount', function ($query)
                        {
                            $query->where('balance', '>', '0');
                        })
                        ->where('due_at', $date)
                        ->whereType('invoice')
                        ->get();

                    Log::info('FI::MailQueue - Invoices found due ' . $daysFromNow . ' days from now on ' . $date . ': ' . $invoices->count());

                    foreach ($invoices as $invoice)
                    {
                        $parser = new Parser($invoice);

                        $contacts = new Contacts($invoice->client);

                        $contactTo = $contacts->getSelectedContactsTo();

                        $mail = $this->mailQueue->create($invoice, [
                            'to'             => $contactTo,
                            'cc'             => [config('fi.mailDefaultCc')],
                            'bcc'            => [config('fi.mailDefaultBcc')],
                            'subject'        => $parser->parse('upcomingPaymentNoticeEmailSubject'),
                            'body'           => $parser->parse('upcomingPaymentNoticeEmailBody'),
                            'attach_pdf'     => config('fi.attachPdf'),
                            'attach_invoice' => config('fi.upcomingPaymentNoticeAttachInvoice') ? config('fi.upcomingPaymentNoticeAttachInvoice') : 0,
                        ]);

                        $this->mailQueue->send($mail->id);
                    }
                }
                else
                {
                    Log::info('FI::MailQueue - Upcoming payment due indicator: ' . $daysFromNow);
                }
            }
        }
    }

    private function queueOverdueInvoices()
    {
        $days = config('fi.overdueInvoiceReminderFrequency');

        if ($days)
        {
            $days = explode(',', $days);

            foreach ($days as $daysAgo)
            {
                $daysAgo = trim($daysAgo);

                if (is_numeric($daysAgo))
                {
                    $daysAgo = intval($daysAgo);

                    $date = Carbon::now()->subDays($daysAgo)->format('Y-m-d');

                    $invoices = Invoice::with('client')
                        ->where('status', '=', 'sent')
                        ->whereHas('amount', function ($query)
                        {
                            $query->where('balance', '>', '0');
                        })
                        ->where('due_at', $date)
                        ->whereType('invoice')
                        ->get();

                    Log::info('FI::MailQueue - Invoices found due ' . $daysAgo . ' days ago on ' . $date . ': ' . $invoices->count());

                    foreach ($invoices as $invoice)
                    {
                        $parser = new Parser($invoice);

                        $contacts = new Contacts($invoice->client);

                        $contactTo = $contacts->getSelectedContactsTo();

                        $mail = $this->mailQueue->create($invoice, [
                            'to'             => $contactTo,
                            'cc'             => [config('fi.mailDefaultCc')],
                            'bcc'            => [config('fi.mailDefaultBcc')],
                            'subject'        => $parser->parse('overdueInvoiceEmailSubject'),
                            'body'           => $parser->parse('overdueInvoiceEmailBody'),
                            'attach_pdf'     => config('fi.attachPdf'),
                            'attach_invoice' => config('fi.overdueAttachInvoice') ? config('fi.overdueAttachInvoice') : 0,
                        ]);

                        $this->mailQueue->send($mail->id);

                        event(new OverdueNoticeEmailed($invoice, $mail));
                    }
                }
                else
                {
                    Log::info('FI::MailQueue - Invalid overdue indicator: ' . $daysAgo);
                }
            }
        }
    }

    private function recurInvoices()
    {
        $recurringInvoices = RecurringInvoice::recurNow()->get();
        $userId            = User::whereUserType('system')->first()->id;

        foreach ($recurringInvoices as $recurringInvoice)
        {
            $tag_ids     = [];
            $invoiceData = [
                'company_profile_id'        => $recurringInvoice->company_profile_id,
                'created_at'                => $recurringInvoice->next_date,
                'document_number_scheme_id' => $recurringInvoice->document_number_scheme_id,
                'user_id'                   => $userId,
                'client_id'                 => $recurringInvoice->client_id,
                'currency_code'             => $recurringInvoice->currency_code,
                'template'                  => $recurringInvoice->template,
                'terms'                     => $recurringInvoice->terms,
                'footer'                    => $recurringInvoice->footer,
                'summary'                   => $recurringInvoice->summary,
                'discount'                  => $recurringInvoice->discount,
                'recurring_invoice_id'      => $recurringInvoice->id,
                'status'                    => $recurringInvoice->client->getAutomaticEmailOnRecur() ? 'sent' : 'draft',
            ];

            $invoice = Invoice::create($invoiceData);

            CustomField::copyCustomFieldValues($recurringInvoice, $invoice);

            $tags                 = $recurringInvoice->tags;
            $recurringInvoiceTags = [];
            foreach ($tags as $tag)
            {
                $recurringInvoiceTags[] = $tag->tag->name;
            }

            if (!empty($recurringInvoiceTags))
            {
                foreach ($recurringInvoiceTags as $tag)
                {
                    $tag = Tag::firstOrNew(['name' => $tag], ['tag_entity' => 'sales'])->fill(['name' => $tag, 'tag_entity' => 'sales']);

                    $tag->save();

                    $tag_ids[] = $tag->id;
                }
                foreach ($tag_ids as $tag_id)
                {
                    $invoice->tags()->create(['invoice_id' => $invoice->id, 'tag_id' => $tag_id]);
                }
            }

            foreach ($recurringInvoice->recurringInvoiceItems as $item)
            {
                $itemData = [
                    'invoice_id'    => $invoice->id,
                    'name'          => $item->name,
                    'description'   => $item->description,
                    'quantity'      => $item->quantity,
                    'price'         => $item->price,
                    'tax_rate_id'   => $item->tax_rate_id,
                    'tax_rate_2_id' => $item->tax_rate_2_id,
                    'display_order' => $item->display_order,
                ];

                InvoiceItem::create($itemData);
            }

            if ($recurringInvoice->stop_date == '0000-00-00' or ($recurringInvoice->stop_date !== '0000-00-00' and ($recurringInvoice->next_date < $recurringInvoice->stop_date)))
            {
                $nextDate = DateFormatter::incrementDate(substr($recurringInvoice->next_date, 0, 10), $recurringInvoice->recurring_period, $recurringInvoice->recurring_frequency);
            }
            else
            {
                $nextDate = '0000-00-00';
            }

            $recurringInvoice->next_date = $nextDate;
            $recurringInvoice->save();

            event(new InvoiceCreatedRecurring($invoice));
        }

        return count($recurringInvoices);
    }

    public function generateTimelineHistory()
    {
        GenerateTimelineHistory::dispatchAfterResponse();
        return response()->json(
            [
                'success' => true,
                'message' => trans('fi.generated_timeline_request_accepted'),
            ], 200);
    }
}
