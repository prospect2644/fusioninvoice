<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Modules\Settings\Controllers;

use FI\Http\Controllers\Controller;
use FI\Modules\MailQueue\Support\MailQueue;
use FI\Modules\Settings\Requests\TestEmailRequest;
use Session;

class TestMailController extends Controller
{

    private $mailQueue;

    public function __construct(MailQueue $mailQueue)
    {
        $this->mailQueue = $mailQueue;
    }

    public function create()
    {

        $fromMail = [
            auth()->user()->name . '###' . auth()->user()->email             => auth()->user()->email,
            config('fi.mailFromName') . '###' . config('fi.mailFromAddress') => config('fi.mailFromAddress'),
        ];
        $to       = [config('fi.testEmailAddress') => config('fi.testEmailAddress')];
        $cc       = [config('fi.mailDefaultCc') => config('fi.mailDefaultCc')];
        $bcc      = [config('fi.mailDefaultBcc') => config('fi.mailDefaultBcc')];
        $subject  = trans('fi.test_email_subject');
        $body     = trans('fi.test_email_body');

        return view('settings._modal_mail')
            ->with([
                'fromMail' => $fromMail,
                'to'       => $to,
                'cc'       => $cc,
                'bcc'      => $bcc,
                'subject'  => $subject,
                'body'     => $body,
            ]);
    }

    public function store(TestEmailRequest $request)
    {

        $from = explode('###', $request->get('from'));

        $testEmailData = [
            'from_email' => $from[1],
            'from_name'  => $from[0],
            'to'         => $request->get('to'),
            'cc'         => is_array($request->get('cc')) ? array_filter($request->get('cc')) : $request->get('cc'),
            'bcc'        => is_array($request->get('bcc')) ? array_filter($request->get('bcc')) : $request->get('bcc'),
            'subject'    => $request->get('subject'),
            'body'       => $request->get('body'),
        ];

        if ($this->mailQueue->sendTestMail($testEmailData))
        {
            return response()->json(
                [
                    'success' => true,
                    'message' => trans('fi.test_mail_sent_successfully'),
                ], 200
            );
            return redirect()
                ->route('settings.index')
                ->with('alertSuccess', trans('fi.test_mail_sent_successfully'));
        }
        else
        {
            return response()->json(
                [
                    'success' => false,
                    'message' => json_encode($this->mailQueue->getError()) . '<br><br>' . $this->mailQueue->getErrorSuggestion(),
                ], 200
            );
            return redirect()->route('settings.index')->with('testEmailError', json_encode($this->mailQueue->getError()) . '<br><br>' . $this->mailQueue->getErrorSuggestion());
        }

    }
}