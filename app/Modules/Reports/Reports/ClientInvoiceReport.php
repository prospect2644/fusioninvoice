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
use FI\Support\CurrencyFormatter;
use FI\Support\DateFormatter;
use FI\Modules\Currencies\Models\Currency;

class ClientInvoiceReport
{
    public function getResults($clientName, $fromDate, $toDate, $companyProfileId = null)
    {

        if ($clientName == 'null' || $clientName == '')
        {
            $clients = Client::all();
        }
        else
        {
            $clients = Client::whereIn('unique_name', explode(',', $clientName))->get();
        }

        $clientData = [];
        foreach ($clients as $client)
        {

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

            $results       = [
                'client_name' => '',
                'from_date'   => '',
                'to_date'     => '',
                'total'       => [],
                'paid'        => [],
                'balance'     => [],
                'records'     => [],
            ];
            $currencyCodes = [];

            foreach ($invoices as $invoice)
            {
                $results['total'][$invoice->currency_code]   = 0;
                $results['paid'][$invoice->currency_code]    = 0;
                $results['balance'][$invoice->currency_code] = 0;
            }

            foreach ($invoices as $invoice)
            {
                $currencyCodes[$invoice->currency_code] = $invoice->currency_code;

                $results['records'][$invoice->currency_code][] = [
                    'formatted_invoice_date' => $invoice->formatted_invoice_date,
                    'number'                 => $invoice->number,
                    'total'                  => $invoice->amount->total,
                    'paid'                   => $invoice->amount->paid,
                    'balance'                => $invoice->amount->balance,
                    'formatted_total'        => $invoice->amount->formatted_total,
                    'formatted_paid'         => $invoice->amount->formatted_paid,
                    'formatted_balance'      => $invoice->amount->formatted_balance,
                    'type'                   => $invoice->type,
                ];

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

                $results['total'][$code]   = CurrencyFormatter::format($results['total'][$code], $currency);
                $results['paid'][$code]    = CurrencyFormatter::format($results['paid'][$code], $currency);
                $results['balance'][$code] = CurrencyFormatter::format($results['balance'][$code], $currency);
            }

            $clientData[$client->name] = $results;

        }

        return $clientData;
    }
}