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

use FI\Modules\RecurringInvoices\Models\RecurringInvoice;
use FI\Support\CurrencyFormatter;
use FI\Support\DateFormatter;
use FI\Support\Frequency;

class RecurringInvoiceReport
{
    public function getResults($fromDate, $toDate, $companyProfileId = null)
    {
        $frequency = Frequency::lists();

        $results = [
            'from_date'           => DateFormatter::format($fromDate),
            'to_date'             => DateFormatter::format($toDate),
            'records'             => [],
            'total_amount'        => [],
            'total_invoice'       => [],
            'grand_total_amount'  => 0,
            'grand_total_invoice' => 0,
        ];

        $items = RecurringInvoice::byDateRange($fromDate, $toDate)
            ->select('recurring_invoices.id', 'clients.name AS client_name', 'recurring_invoices.summary',
                'recurring_invoices.next_date', 'recurring_invoices.stop_date', 'recurring_invoice_amounts.total',
                'recurring_invoices.recurring_frequency', 'recurring_invoices.recurring_period',
                'recurring_invoices.exchange_rate')
            ->join('recurring_invoice_amounts', 'recurring_invoices.id', '=', 'recurring_invoice_amounts.recurring_invoice_id')
            ->join('clients', 'clients.id', '=', 'recurring_invoices.client_id')
            ->orderBy('recurring_invoices.recurring_period')
            ->orderBy('recurring_invoices.recurring_frequency');

        if ($companyProfileId)
        {
            $items->where('recurring_invoices.company_profile_id', $companyProfileId);
        }

        $items = $items->get();

        foreach ($items as $item)
        {
            $results['records'][$frequency[$item->recurring_period]][$item->recurring_frequency][] = [
                'id'                  => $item->id,
                'client_name'         => $item->client_name,
                'summary'             => $item->summary,
                'next_date'           => DateFormatter::format($item->next_date),
                'stop_date'           => $item->stop_date != '0000-00-00' ? DateFormatter::format($item->stop_date) : null,
                'total'               => CurrencyFormatter::format($item->total / $item->exchange_rate),
                'recurring_period'    => $item->recurring_period,
                'recurring_frequency' => $item->recurring_frequency,
                'invoice_total'       => round($item->total / $item->exchange_rate, 2),
            ];
        }

        foreach ($results['records'] as $period => $records)
        {
            foreach ($records as $frequency => $period_data)
            {
                foreach ($period_data as $key => $data)
                {
                    $results['total_amount'][$period][$frequency]  = isset($results['total_amount'][$period][$frequency]) ? $results['total_amount'][$period][$frequency] + $data['invoice_total'] : $data['invoice_total'];
                    $results['total_invoice'][$period][$frequency] = isset($results['total_invoice'][$period][$frequency]) ? $results['total_invoice'][$period][$frequency] + 1 : 1;
                    $results['grand_total_amount']                 = $results['grand_total_amount'] + $data['invoice_total'];
                    $results['grand_total_invoice']                = $results['grand_total_invoice'] + 1;
                }

                $results['total_amount'][$period][$frequency] = CurrencyFormatter::format($results['total_amount'][$period][$frequency]);
            }

        }

        $results['grand_total_amount'] = CurrencyFormatter::format($results['grand_total_amount']);

        return $results;
    }
}