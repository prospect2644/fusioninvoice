<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Modules\Reports\Reports;

use FI\Modules\Clients\Models\Client;
use FI\Modules\Payments\Models\Payment;
use FI\Support\CurrencyFormatter;
use FI\Support\DateFormatter;
use FI\Modules\Currencies\Models\Currency;

class ClientStatementReport
{
    public function getResults($clientName, $fromDate, $toDate, $companyProfileId = null)
    {
        $results       = [
            'client_name' => '',
            'from_date'   => '',
            'to_date'     => '',
            'subtotal'    => [],
            'discount'    => [],
            'tax'         => [],
            'total'       => [],
            'paid'        => [],
            'balance'     => [],
            'records'     => [],
            'prepayments' => [],
        ];
        $currencyCodes = [];

        $client = Client::where('unique_name', $clientName)->first();

        $invoices = $client->invoices()
            ->with('items', 'client.currency', 'amount.invoice.currency')
            ->notCanceled()
            ->where('invoice_date', '>=', $fromDate)
            ->where('invoice_date', '<=', $toDate)
            ->orderBy('invoice_date');

        if ($companyProfileId)
        {
            $invoices->where('company_profile_id', $companyProfileId);
        }

        $invoices = $invoices->get();

        foreach ($invoices as $invoice)
        {
            $results['subtotal'][$invoice->currency_code] = 0;
            $results['discount'][$invoice->currency_code] = 0;
            $results['tax'][$invoice->currency_code]      = 0;
            $results['total'][$invoice->currency_code]    = 0;
            $results['paid'][$invoice->currency_code]     = 0;
            $results['balance'][$invoice->currency_code]  = 0;
        }

        foreach ($invoices as $invoice)
        {
            $currencyCodes[$invoice->currency_code] = $invoice->currency_code;

            $results['records'][$invoice->currency_code][] = [
                'formatted_invoice_date' => $invoice->formatted_invoice_date,
                'number'                 => $invoice->number,
                'summary'                => $invoice->summary,
                'subtotal'               => $invoice->amount->subtotal,
                'discount'               => $invoice->amount->discount,
                'tax'                    => $invoice->amount->tax,
                'total'                  => $invoice->amount->total,
                'paid'                   => $invoice->amount->paid,
                'balance'                => $invoice->amount->balance,
                'formatted_subtotal'     => $invoice->amount->formatted_subtotal,
                'formatted_discount'     => $invoice->amount->formatted_discount,
                'formatted_tax'          => $invoice->amount->formatted_tax,
                'formatted_total'        => $invoice->amount->formatted_total,
                'formatted_paid'         => $invoice->amount->formatted_paid,
                'formatted_balance'      => $invoice->amount->formatted_balance,
                'type'                   => $invoice->type,
            ];

            $results['subtotal'][$invoice->currency_code] += $invoice->amount->subtotal;
            $results['discount'][$invoice->currency_code] += $invoice->amount->discount;
            $results['tax'][$invoice->currency_code] += $invoice->amount->tax;
            $results['total'][$invoice->currency_code] += $invoice->amount->total;
            $results['paid'][$invoice->currency_code] += $invoice->amount->paid;
            $results['balance'][$invoice->currency_code] += $invoice->amount->balance;
        }

        $results['client_name'] = $client->name;
        $results['from_date']   = DateFormatter::format($fromDate);
        $results['to_date']     = DateFormatter::format($toDate);

        foreach ($currencyCodes as $code)
        {
            $currency = Currency::whereCode($code)->first();

            $results['subtotal'][$code] = CurrencyFormatter::format($results['subtotal'][$code], $currency);
            $results['discount'][$code] = CurrencyFormatter::format($results['discount'][$code], $currency);
            $results['tax'][$code]      = CurrencyFormatter::format($results['tax'][$code], $currency);
            $results['total'][$code]    = CurrencyFormatter::format($results['total'][$code], $currency);
            $results['paid'][$code]     = CurrencyFormatter::format($results['paid'][$code], $currency);
            $results['balance'][$code]  = CurrencyFormatter::format($results['balance'][$code], $currency);
        }

        $prePayments = Payment::select('payments.*')
            ->whereType('pre-payment')
            ->whereClientId($client->id)
            ->dateRange($fromDate, $toDate)
            ->orderBy('payments.paid_at')->get();

        if ($prePayments->count() > 0)
        {
            $results['prepayments']['total'][$client->currency_code]   = 0;
            $results['prepayments']['balance'][$client->currency_code] = 0;
        }

        foreach ($prePayments as $payment)
        {
            $currencyCodes[$client->currency_code] = $client->currency_code;

            $results['prepayments']['records'][$client->currency_code][] = [
                'formatted_invoice_date' => $payment->formatted_paid_at,
                'total'                  => $payment->amount,
                'balance'                => $payment->remaining_balance,
                'formatted_total'        => $payment->formatted_amount,
                'formatted_balance'      => $payment->formatted_remaining_balance,
            ];

            $results['prepayments']['total'][$client->currency_code] += $payment->amount;
            $results['prepayments']['balance'][$client->currency_code] += $payment->remaining_balance;
        }


        return $results;
    }
}