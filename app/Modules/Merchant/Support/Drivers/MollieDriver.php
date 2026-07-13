<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Modules\Merchant\Support\Drivers;

use Carbon\Carbon;
use FI\Modules\Invoices\Models\Invoice;
use FI\Modules\Merchant\Models\MerchantPayment;
use FI\Modules\Merchant\Support\MerchantDriver;
use FI\Modules\Payments\Events\PaymentEmailed;
use FI\Modules\Payments\Models\Payment;
use FI\Modules\Payments\Models\PaymentInvoice;
use FI\Modules\Users\Models\User;
use Mollie\Api\MollieApiClient;

class MollieDriver extends MerchantDriver
{
    public function getSettings()
    {
        return ['apiKey'];
    }

    public function pay(Invoice $invoice)
    {
        $mollie = new MollieApiClient();

        $mollie->setApiKey($this->getSetting('apiKey'));

        $payment = $mollie->payments->create([
            'amount'      => [
                'currency' => $invoice->currency_code,
                'value'    => number_format($invoice->amount->balance, 2, '.', ''),
            ],
            'description' => trans('fi.invoice') . ' #' . $invoice->number,
            'redirectUrl' => route('merchant.pay.mollie.return', [$invoice->url_key]),
            'webhookUrl'  => route('merchant.pay.mollie.webhook', [$invoice->url_key]),
        ]);

        return $payment->getCheckoutUrl();
    }

    public function verify(Invoice $invoice)
    {
        $mollie = new MollieApiClient();

        $mollie->setApiKey($this->getSetting('apiKey'));

        $payment = $mollie->payments->get(request('id'));

        if ($payment->isPaid() && $invoice->status != 'paid')
        {
            $userId = User::whereUserType('system')->first()->id;

            $fiPayment = Payment::create([
                'user_id'           => $userId,
                'client_id'         => $invoice->client->id,
                'amount'            => $payment->amount->value,
                'remaining_balance' => 0,
                'payment_method_id' => config('fi.onlinePaymentMethod'),
                'paid_at'           => Carbon::now()->format('Y-m-d'),
            ]);
            if ($fiPayment)
            {
                $paymentInvoice                      = new PaymentInvoice();
                $paymentInvoice->payment_id          = $fiPayment->id;
                $paymentInvoice->invoice_id          = $invoice->id;
                $paymentInvoice->invoice_amount_paid = $payment->amount->value;

                $paymentInvoice->save();
                if ($fiPayment->client->should_email_payment_receipt)
                {
                    event(new PaymentEmailed($fiPayment));
                }
            }
            MerchantPayment::saveByKey($this->getName(), $fiPayment->id, 'id', $payment->id);
        }
    }
}