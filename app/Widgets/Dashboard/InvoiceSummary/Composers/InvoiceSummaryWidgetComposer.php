<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Widgets\Dashboard\InvoiceSummary\Composers;

use FI\Modules\Invoices\Models\InvoiceAmount;
use FI\Modules\Payments\Models\PaymentInvoice;
use FI\Support\CurrencyFormatter;
use Illuminate\Support\Facades\DB;

class InvoiceSummaryWidgetComposer
{
    public function compose($view)
    {
        $view->with('invoicesTotalDraft', $this->getInvoicesTotalDraft())
            ->with('invoicesTotalSent', $this->getInvoicesTotalSent())
            ->with('invoicesTotalPaid', $this->getInvoicesTotalPaid())
            ->with('invoicesTotalOverdue', $this->getInvoicesTotalOverdue())
            ->with('invoiceDashboardTotalOptions', periods());
    }

    private function getInvoicesTotalDraft()
    {
        return CurrencyFormatter::format(InvoiceAmount::join('invoices', 'invoices.id', '=', 'invoice_amounts.invoice_id')
            ->whereHas('invoice', function ($q)
            {
                $q->draft();
                switch (config('fi.dashboardWidgetsDateOption'))
                {
                    case 'year_to_date':
                        $q->thisYear();
                        break;
                    case 'this_quarter':
                        $q->thisQuarter();
                        break;
                    case 'this_month':
                        $q->thisMonth();
                        break;
                    case 'last_year':
                        $q->lastYear();
                        break;
                    case 'last_quarter':
                        $q->lastQuarter();
                        break;
                    case 'last_month':
                        $q->lastMonth();
                        break;
                    case 'custom_date_range':
                        $q->dateRange(config('fi.dashboardWidgetsFromDate'), config('fi.dashboardWidgetsToDate'));
                        break;
                }
            })->sum(DB::raw('balance / exchange_rate')));
    }

    private function getInvoicesTotalSent()
    {
        return CurrencyFormatter::format(InvoiceAmount::join('invoices', 'invoices.id', '=', 'invoice_amounts.invoice_id')
            ->whereHas('invoice', function ($q)
            {
                $q->sent();
                switch (config('fi.dashboardWidgetsDateOption'))
                {
                    case 'year_to_date':
                        $q->thisYear();
                        break;
                    case 'this_quarter':
                        $q->thisQuarter();
                        break;
                    case 'this_month':
                        $q->thisMonth();
                        break;
                    case 'last_year':
                        $q->lastYear();
                        break;
                    case 'last_quarter':
                        $q->lastQuarter();
                        break;
                    case 'last_month':
                        $q->lastMonth();
                        break;
                    case 'custom_date_range':
                        $q->dateRange(config('fi.dashboardWidgetsFromDate'), config('fi.dashboardWidgetsToDate'));
                        break;
                }
            })->sum(DB::raw('balance / exchange_rate')));
    }

    private function getInvoicesTotalPaid()
    {
        $payments = PaymentInvoice::join('invoices', 'invoices.id', '=', 'payment_invoices.invoice_id')->whereHas('payment', function ($q)
        {
            $q->whereIn('type', ['single', 'pre-payment']);
            switch (config('fi.dashboardWidgetsDateOption'))
            {
                case 'year_to_date':
                    $q->thisYear();
                    break;
                case 'this_quarter':
                    $q->thisQuarter();
                    break;
                case 'this_month':
                    $q->thisMonth();
                    break;
                case 'last_year':
                    $q->lastYear();
                    break;
                case 'last_quarter':
                    $q->lastQuarter();
                    break;
                case 'last_month':
                    $q->lastMonth();
                    break;
                case 'custom_date_range':
                    $q->dateRange(config('fi.dashboardWidgetsFromDate'), config('fi.dashboardWidgetsToDate'));
                    break;
            }
        });


        return CurrencyFormatter::format($payments->sum(DB::raw('invoice_amount_paid / exchange_rate')));
    }

    private function getInvoicesTotalOverdue()
    {
        return CurrencyFormatter::format(InvoiceAmount::join('invoices', 'invoices.id', '=', 'invoice_amounts.invoice_id')
            ->whereHas('invoice', function ($q)
            {
                $q->overdue();
                switch (config('fi.dashboardWidgetsDateOption'))
                {
                    case 'year_to_date':
                        $q->thisYearOverdue();
                        break;
                    case 'this_quarter':
                        $q->thisQuarterOverdue();
                        break;
                    case 'this_month':
                        $q->thisMonthOverdue();
                        break;
                    case 'last_year':
                        $q->lastYearOverdue();
                        break;
                    case 'last_quarter':
                        $q->lastQuarterOverdue();
                        break;
                    case 'last_month':
                        $q->lastMonthOverdue();
                        break;
                    case 'custom_date_range':
                        $q->dateRangeOverdue(config('fi.dashboardWidgetsFromDate'), config('fi.dashboardWidgetsToDate'));
                        break;
                }
            })->sum(DB::raw('balance / exchange_rate')));
    }
}