<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Modules\Payments\Controllers;

use FI\Http\Controllers\Controller;
use FI\Modules\MailQueue\Support\MailQueue;
use FI\Modules\Payments\Events\AddTransition;
use FI\Modules\Payments\Models\Payment;
use FI\Requests\SendEmailRequest;
use FI\Support\Contacts;
use FI\Support\Parser;

class PaymentMailController extends Controller
{
    private $mailQueue;

    public function __construct(MailQueue $mailQueue)
    {
        $this->mailQueue = $mailQueue;
    }

    public function create()
    {
        $payment = Payment::find(request('payment_id'));

        $contacts = new Contacts($payment->client);

        $parser = new Parser($payment);

        if (strtolower($payment->user->name) === 'system')
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
                $payment->user->name . '###' . $payment->user->email             => $payment->user->email,
                auth()->user()->name . '###' . auth()->user()->email             => auth()->user()->email,
            ];
        }

        return view('payments._modal_mail')
            ->with('paymentId', $payment->id)
            ->with('redirectTo', request('redirectTo'))
            ->with('subject', $parser->parse('paymentReceiptEmailSubject'))
            ->with('body', (config('fi.paymentReceiptBody') == 'default') ? $parser->parse('paymentReceiptBody', 'default') : $parser->parse('paymentReceiptBody', 'custom'))
            ->with('contactDropdownTo', $contacts->contactDropdownTo(false))
            ->with('contactDropdownCc', $contacts->contactDropdownCc())
            ->with('contactDropdownBcc', $contacts->contactDropdownBcc())
            ->with('fromMail', $fromMail);
    }

    public function store(SendEmailRequest $request)
    {
        $payment = Payment::find($request->input('payment_id'));

        $mail = $this->mailQueue->create($payment, $request->except('payment_id'));

        event(new AddTransition($payment, 'payment_receipt_email_sent'));

        if (!$this->mailQueue->send($mail->id))
        {
            return response()->json(['errors' => [[$this->mailQueue->getError()]]], 400);
        }
    }
}
