<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Modules\Invoices\Controllers;

use FI\Http\Controllers\Controller;
use FI\Modules\Invoices\Events\AddTransition;
use FI\Modules\Invoices\Events\InvoiceEmailed;
use FI\Modules\Invoices\Events\InvoiceEmailing;
use FI\Modules\Invoices\Models\Invoice;
use FI\Modules\MailQueue\Support\MailQueue;
use FI\Requests\SendEmailRequest;
use FI\Support\Contacts;
use FI\Support\Parser;

class InvoiceMailController extends Controller
{
    private $mailQueue;

    public function __construct(MailQueue $mailQueue)
    {
        $this->mailQueue = $mailQueue;
    }

    public function create()
    {
        $invoice = Invoice::find(request('invoice_id'));

        $contacts = new Contacts($invoice->client);

        $parser = new Parser($invoice);

        if ($invoice->type == 'credit_memo')
        {
            $subject = $parser->parse('creditMemoEmailSubject');
            $body    = $parser->parse('creditMemoEmailBody');
        }
        else
        {
            if (!$invoice->is_overdue)
            {
                $subject = $parser->parse('invoiceEmailSubject');
                $body    = $parser->parse('invoiceEmailBody');
            }
            else
            {
                $subject = $parser->parse('overdueInvoiceEmailSubject');
                $body    = $parser->parse('overdueInvoiceEmailBody');
            }
        }

        if (strtolower($invoice->user->name) === 'system')
        {
            $fromMail = [
                config('fi.mailFromName') . '###' . config('fi.mailFromAddress') => config('fi.mailFromAddress'),
                auth()->user()->name . '###' . auth()->user()->email             => auth()->user()->email,
            ];
        }
        else
        {
            $fromMail = [
                config('fi.mailFromName') . '###' . config('fi.mailFromAddress') => config('fi.mailFromAddress'),
                $invoice->user->name . '###' . $invoice->user->email             => $invoice->user->email,
                auth()->user()->name . '###' . auth()->user()->email             => auth()->user()->email,
            ];
        }

        return view('invoices._modal_mail')
            ->with('invoice', $invoice)
            ->with('redirectTo', urlencode(request('redirectTo')))
            ->with('subject', $subject)
            ->with('body', $body)
            ->with('contactDropdownTo', $contacts->contactDropdownTo())
            ->with('contactDropdownCc', $contacts->contactDropdownCc())
            ->with('contactDropdownBcc', $contacts->contactDropdownBcc())
            ->with('fromMail', $fromMail);
    }

    public function store(SendEmailRequest $request)
    {
        $invoice = Invoice::find($request->input('invoice_id'));

        $contacts = new Contacts($invoice->client);

        $contactTo = $contacts->getAllContacts();

        if (count($request->get('to')) > 1)
        {
            $body = trans('fi.default_greeting') . $request->get('body');
        }
        else
        {
            if (isset($contactTo[$request->get('to')[0]]))
            {
                $contactName = explode(' <', $contactTo[$request->get('to')[0]]);
                $body        = trans('fi.hi') . current($contactName) . '<br>' . $request->get('body');
            }
            else
            {
                $body = trans('fi.hi') . '<br>' . $request->get('body');
            }

        }

        $input = $request->except('invoice_id');

        $input['body'] = $body;

        event(new InvoiceEmailing($invoice));

        $mail = $this->mailQueue->create($invoice, $input);

        if ($this->mailQueue->send($mail->id))
        {
            event(new InvoiceEmailed($invoice));
            event(new AddTransition($invoice, 'email_sent'));
        }
        else
        {
            return response()->json(['errors' => [[$this->mailQueue->getError()]]], 400);
        }
    }
}
