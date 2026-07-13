<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Modules\MailQueue\Support;

use Artisan;
use Exception;
use FI\Support\FileNames;
use FI\Support\PDF\PDFFactory;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use ZipArchive;

class MailQueue
{
    protected $error;
    protected $errorSuggestions;

    public function create($object, $input)
    {
        if (isset($input['mail_from']) && $input['mail_from'] != '')
        {
            $from = explode('###', $input['mail_from']);
            $from = [
                'email' => $from[1], 'name' => $from[0],
            ];
        }
        else
        {
            if (config('fi.mailFromAddress') == '')
            {
                $from = ['email' => $object->user->email, 'name' => $object->user->name];
            }
            else
            {
                $from = [
                    'email' => config('fi.mailFromAddress'), 'name' => config('fi.mailFromName'),
                ];
            }
        }

        return $object->mailQueue()->create([
            'from'           => json_encode($from),
            'to'             => json_encode($input['to'], JSON_FORCE_OBJECT),
            'cc'             => json_encode(($input['cc']) ?: [], JSON_FORCE_OBJECT),
            'bcc'            => json_encode(($input['bcc']) ?: [], JSON_FORCE_OBJECT),
            'subject'        => $input['subject'],
            'body'           => $input['body'],
            'attach_pdf'     => $input['attach_pdf'],
            'attach_invoice' => ($input['attach_invoice']) ?? 0,
        ]);
    }

    public function send($id)
    {
        $mail = \FI\Modules\MailQueue\Models\MailQueue::find($id);

        if ($this->sendMail(
            $mail->from,
            $mail->to,
            $mail->cc,
            $mail->bcc,
            $mail->subject,
            $mail->body,
            $this->getAttachmentPath($mail),
            $this->getAttachInvoicePath($mail)
        )
        )
        {
            $mail->sent = 1;
            $mail->save();

            return true;
        }

        return false;
    }

    private function getAttachmentPath($mail)
    {
        if ($mail->attach_pdf)
        {
            $object = $mail->mailable;

            $pdfPath = base_path('storage/' . $object->pdf_filename);

            $pdf = PDFFactory::create();

            $pdf->save($object->html, $pdfPath);

            return $pdfPath;
        }

        return null;
    }

    private function createZip($paymentInvoices)
    {
        $ids = [];
        foreach ($paymentInvoices as $paymentInvoice)
        {
            $ids[] = $paymentInvoice->invoice_id;
        }

        $zipFileName = reset($ids) . '_' . end($ids) . '_invoices.zip';

        try
        {
            // Initializing PHP class
            $zip = new ZipArchive();

            $res = $zip->open(storage_path('app/public/') . $zipFileName, ZipArchive::CREATE | ZipArchive::OVERWRITE);

            if (!$res === true)
            {
                // write to log that creating zip file failed.  If app/pubic folder does not exist, this fails with $res = 5
                Log::error('There was an error creating the zip file. Zip return code: ' . $res);
                return false;
            }
            else
            {
                $unlinkFiles = [];

                foreach ($paymentInvoices as $paymentInvoice)
                {
                    $invoice = $paymentInvoice->invoice;
                    // This allows us to select invoices created by other users, but would fail trying to zip them.
                    $pdf            = PDFFactory::create();
                    $invoicePdf     = FileNames::invoice($invoice);
                    $invoicePdfPath = base_path('assets/' . $invoicePdf);
                    $unlinkFiles[]  = $invoicePdfPath;
                    $pdf->save($invoice->html, $invoicePdfPath);
                    $zip->addFile($invoicePdfPath, $invoicePdf);
                }
                $zip->close();

                foreach ($unlinkFiles as $file)
                {
                    if (file_exists($file))
                    {
                        unlink($file);
                    }
                }

                return storage_path('app/public/' . $zipFileName);
            }
        }
        catch (\Exception $e)
        {
            Log::error('Zip operation error on open: ' . $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine());
            return false;
        }
    }

    private function getAttachInvoicePath($mail)
    {
        $invoiceAttachmentPath = false;
        if ($mail->attach_invoice)
        {
            $object = $mail->mailable;
            if ($mail->mailable_type == 'FI\Modules\Payments\Models\Payment')
            {
                $paymentInvoices = $object->paymentInvoice;
                if (count($paymentInvoices) > 1)
                {
                    $invoiceAttachmentPath = $this->createZip($paymentInvoices);
                }
                elseif (count($paymentInvoices) == 1)
                {
                    $invoice = $paymentInvoices->first()->invoice;

                    $pdf = PDFFactory::create();

                    $invoiceAttachmentPath = storage_path('app/public/') . FileNames::invoice($invoice);

                    $pdf->save($invoice->html, $invoiceAttachmentPath);
                }
            }
            elseif ($mail->mailable_type == 'FI\Modules\Invoices\Models\Invoice')
            {
                $invoice = $object;

                $pdf = PDFFactory::create();

                $invoiceAttachmentPath = storage_path('app/public/') . FileNames::invoice($invoice);

                $pdf->save($invoice->html, $invoiceAttachmentPath);
            }
        }
        return $invoiceAttachmentPath;
    }

    private function sendMail($from, $to, $cc, $bcc, $subject, $body, $attachmentPath = null, $invoiceAttachmentPath = null)
    {
        try
        {
            $htmlTemplate = (view()->exists('email_templates.html')) ? 'email_templates.html' : 'templates.emails.html';

            Mail::send([$htmlTemplate, 'templates.emails.text'], ['body' => $body], function ($message) use ($from, $to, $cc, $bcc, $subject, $attachmentPath, $invoiceAttachmentPath)
            {
                $from = json_decode($from, true);
                $to   = json_decode($to, true);
                $cc   = json_decode($cc, true);
                $bcc  = json_decode($bcc, true);

                $message->from($from['email'], $from['name']);
                $message->subject($subject);

                foreach ($to as $toRecipient)
                {
                    $message->to(trim($toRecipient));
                }

                foreach ($cc as $ccRecipient)
                {
                    if ($ccRecipient !== '')
                    {
                        $message->cc(trim($ccRecipient));
                    }
                }

                foreach ($bcc as $bccRecipient)
                {
                    if ($bccRecipient !== '')
                    {
                        $message->bcc(trim($bccRecipient));
                    }
                }

                if (config('fi.mailReplyToAddress'))
                {
                    $message->replyTo(config('fi.mailReplyToAddress'));
                }

                if ($attachmentPath)
                {
                    $message->attach($attachmentPath);
                }
                if ($invoiceAttachmentPath)
                {
                    $message->attach($invoiceAttachmentPath);
                }
            });

            if ($attachmentPath and file_exists($attachmentPath))
            {
                unlink($attachmentPath);
            }
            if ($invoiceAttachmentPath and file_exists($invoiceAttachmentPath))
            {
                unlink($invoiceAttachmentPath);
            }

            return true;
        }
        catch (Exception $e)
        {
            $this->error = $e->getMessage();

            $this->errorSuggestions = $this->setErrorSuggestion();

            return false;
        }
    }

    public function sendTestMail($mailData)
    {

        try
        {
            Artisan::call('config:clear');

            $htmlTemplate = (view()->exists('email_templates.html')) ? 'email_templates.html' : 'templates.emails.html';

            Mail::send([$htmlTemplate, 'templates.emails.text'], ['body' => $mailData['body']], function ($message) use ($mailData)
            {
                $message->from($mailData['from_email'], $mailData['from_name']);
                $message->subject($mailData['subject']);
                $message->to($mailData['to']);
                if (!empty($mailData['cc']))
                {
                    $message->cc($mailData['cc']);
                }
                if (!empty($mailData['bcc']))
                {
                    $message->bcc($mailData['bcc']);
                }
            });

            return true;
        }
        catch (Exception $e)
        {

            $this->error = $e->getMessage() . $e->getFile() . $e->getLine();

            $this->errorSuggestions = $this->setErrorSuggestion();

            return false;
        }
    }

    public function setErrorSuggestion()
    {
        switch (config('fi.mailDriver'))
        {
            case 'smtp':
                return trans('fi.smtp-setting-suggestion');
            case 'mail':
                return trans('fi.mail-setting-suggestion');
            case 'sendmail':
                return trans('fi.sendmail-setting-suggestion');
            case 'sendgrid':
                return trans('fi.sendgrid-setting-suggestion');
        }
    }

    public function getError()
    {
        return $this->error;
    }

    public function getErrorSuggestion()
    {
        return $this->errorSuggestions;
    }
}
