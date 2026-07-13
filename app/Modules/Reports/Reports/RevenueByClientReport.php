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

use FI\Modules\Payments\Models\Payment;
use FI\Support\CurrencyFormatter;

class RevenueByClientReport
{
    public function getResults($fromDate, $toDate, $companyProfileId = null)
    {
        $results = [
            'clients'     => [],
            'grand_total' => 0,
        ];

        $payments = Payment::select('payments.*')
            ->with(['paymentInvoice.invoice.client'])
            ->join('payment_invoices', 'payments.id', '=', 'payment_invoices.payment_id')
            ->join('invoices', 'invoices.id', '=', 'payment_invoices.invoice_id')
            ->join('clients', 'clients.id', '=', 'invoices.client_id')
            ->where('paid_at', '>=', $fromDate)
            ->where('paid_at', '<=', $toDate)
            ->orderBy('clients.name');

        if ($companyProfileId)
        {
            $payments->where('company_profile_id', $companyProfileId);
        }

        $payments = $payments->get();

        foreach ($payments as $payment)
        {
            foreach ($payment->paymentInvoice as $paymentInvoice)
            {
                if (isset($results[$paymentInvoice->invoice->client->name][date('Y', strtotime($paymentInvoice->created_at))]['months'][date('n', strtotime($paymentInvoice->created_at))]))
                {
                    $results['clients'][$paymentInvoice->invoice->client->name][date('Y', strtotime($paymentInvoice->created_at))]['months'][date('n', strtotime($paymentInvoice->created_at))] += $paymentInvoice->invoice_amount_paid / $paymentInvoice->invoice->exchange_rate;
                }
                else
                {
                    $results['clients'][$paymentInvoice->invoice->client->name][date('Y', strtotime($paymentInvoice->created_at))]['months'][date('n', strtotime($paymentInvoice->created_at))] = $paymentInvoice->invoice_amount_paid / $paymentInvoice->invoice->exchange_rate;
                }
            }
        }

        foreach ($results['clients'] as $client => $yearlyData)
        {
            foreach ($yearlyData as $year => $result)
            {

                $results['clients'][$client][$year]['total'] = 0;

                foreach (range(1, 12) as $month)
                {
                    if (!isset($results['clients'][$client][$year]['months'][$month]))
                    {
                        $results['clients'][$client][$year]['months'][$month] = CurrencyFormatter::format(0);
                    }
                    else
                    {
                        $results['clients'][$client][$year]['total'] += $results['clients'][$client][$year]['months'][$month];
                        $results['clients'][$client][$year]['months'][$month] = CurrencyFormatter::format($results['clients'][$client][$year]['months'][$month]);
                    }
                }

                $results['grand_total'] += $results['clients'][$client][$year]['total'];

                $results['clients'][$client][$year]['total'] = CurrencyFormatter::format($results['clients'][$client][$year]['total']);

            }

        }

        $results['grand_total'] = CurrencyFormatter::format($results['grand_total']);

        return $results;
    }
}