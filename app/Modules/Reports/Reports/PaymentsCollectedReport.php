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
use FI\Support\DateFormatter;

class PaymentsCollectedReport
{
    public function getResults($fromDate, $toDate, $companyProfileId = null, $prepayments = null, $currency_format = null)
    {
        $results = [
            'from_date'       => DateFormatter::format($fromDate),
            'to_date'         => DateFormatter::format($toDate),
            'payments'        => [],
            'grandTotal'      => 0,
            'records'         => [],
            'currency_format' => $currency_format,
        ];

        $payments = Payment::select('payments.*')
            ->with(['paymentInvoice.invoice.client', 'paymentMethod'])
            ->join('payment_invoices', 'payments.id', '=', 'payment_invoices.payment_id')
            ->join('invoices', 'payment_invoices.invoice_id', '=', 'invoices.id')
            ->dateRange($fromDate, $toDate)
            ->where('payments.type', '!=', 'credit-memo')
            ->orderBy('payments.payment_method_id')
            ->orderBy('payments.paid_at', 'desc');

        if ($companyProfileId)
        {
            $payments->where('invoices.company_profile_id', $companyProfileId);
        }

        $payments = $payments->get();

        foreach ($payments as $payment)
        {

            $paymentMethodName = $payment->paymentMethod->name;

            if ($payment->paymentInvoice && count($payment->paymentInvoice) > 0 && $payment->type != 'pre-payment')
            {
                $results = self::calculatePayments($payment, $results, $paymentMethodName);
            }
            elseif ($payment->type == 'pre-payment')
            {
                $results = self::calculatePrePayments($payment, $results, trans('fi.pre_payment'), $prepayments);
            }

        }

        foreach ($results['records'] as $key => $result)
        {
            $results['records'][$key]['totals']['amount'] = CurrencyFormatter::format($results['records'][$key]['totals']['amount']);
        }

        $results['grandTotal'] = CurrencyFormatter::format($results['grandTotal']);

        return $results;
    }

    public static function calculatePrePayments($payment, $results, $paymentMethodName, $prepayments)
    {
        if ($prepayments != 'include_prepayments')
        {
            $results = self::calculatePayments($payment, $results, $paymentMethodName);
        }
        else
        {
            $results['records'][$paymentMethodName]['payments'][] = [
                'client_name'          => $payment->client->name,
                'invoice_number'       => '',
                'payment_method'       => $payment->paymentMethod->name,
                'note'                 => $payment->note,
                'date'                 => $payment->formatted_paid_at,
                'amount_with_currency' => $payment->formatted_amount_with_currency,
                'amount'               => CurrencyFormatter::format($payment->amount),
            ];

            $results['grandTotal'] += $payment->amount;

            if (isset($results['records'][$paymentMethodName]['totals']))
            {
                $results['records'][$paymentMethodName]['totals']['amount'] += $payment->amount;
            }
            else
            {
                $results['records'][$paymentMethodName]['totals']['amount'] = $payment->amount;
            }
        }

        return $results;

    }

    public static function calculatePayments($payment, $results, $paymentMethodName)
    {
        foreach ($payment->paymentInvoice as $paymentInvoice)
        {
            $results['records'][$paymentMethodName]['payments'][] = [
                'client_name'          => $paymentInvoice->invoice->client->name,
                'invoice_number'       => $paymentInvoice->invoice->number,
                'payment_method'       => $paymentMethodName,
                'note'                 => $payment->note,
                'date'                 => $payment->formatted_paid_at,
                'amount_with_currency' => $payment->formatted_amount_with_currency,
                'amount'               => CurrencyFormatter::format($paymentInvoice->invoice_amount_paid / $paymentInvoice->invoice->exchange_rate),
            ];

            $results['grandTotal'] += $paymentInvoice->invoice_amount_paid / $paymentInvoice->invoice->exchange_rate;

            if (isset($results['records'][$paymentMethodName]['totals']))
            {
                $results['records'][$paymentMethodName]['totals']['amount'] += $paymentInvoice->invoice_amount_paid;
            }
            else
            {
                $results['records'][$paymentMethodName]['totals']['amount'] = $paymentInvoice->invoice_amount_paid;
            }

        }

        return $results;
    }
}