<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Widgets\Dashboard\QuoteSummary\Composers;

use FI\Modules\Quotes\Models\QuoteAmount;
use FI\Support\CurrencyFormatter;
use Illuminate\Support\Facades\DB;

class QuoteSummaryWidgetComposer
{
    public function compose($view)
    {
        $view->with('quotesTotalDraft', $this->getQuoteTotalDraft())
            ->with('quotesTotalSent', $this->getQuoteTotalSent())
            ->with('quotesTotalApproved', $this->getQuoteTotalApproved())
            ->with('quotesTotalRejected', $this->getQuoteTotalRejected())
            ->with('quoteDashboardTotalOptions', periods());
    }

    private function getQuoteTotalDraft()
    {
        return CurrencyFormatter::format(QuoteAmount::join('quotes', 'quotes.id', '=', 'quote_amounts.quote_id')
            ->whereHas('quote', function ($q)
            {
                $q->draft();
                $q->where('invoice_id', 0);
                switch (config('fi.dashboardWidgetsDateOption'))
                {
                    case 'year_to_date':
                        $q->thisYear();
                        break;
                    case 'this_month':
                        $q->thisMonth();
                        break;
                    case 'last_month':
                        $q->lastMonth();
                        break;
                    case 'this_quarter':
                        $q->thisQuarter();
                        break;
                    case 'last_year':
                        $q->lastYear();
                        break;
                    case 'last_quarter':
                        $q->lastQuarter();
                        break;
                    case 'custom_date_range':
                        $q->dateRange(config('fi.dashboardWidgetsFromDate'), config('fi.dashboardWidgetsToDate'));
                        break;
                }
            })->sum(DB::raw('total / exchange_rate')));
    }

    private function getQuoteTotalSent()
    {
        return CurrencyFormatter::format(QuoteAmount::join('quotes', 'quotes.id', '=', 'quote_amounts.quote_id')
            ->whereHas('quote', function ($q)
            {
                $q->sent();
                $q->where('invoice_id', 0);
                switch (config('fi.dashboardWidgetsDateOption'))
                {
                    case 'year_to_date':
                        $q->thisYear();
                        break;
                    case 'this_month':
                        $q->thisMonth();
                        break;
                    case 'last_month':
                        $q->lastMonth();
                        break;
                    case 'this_quarter':
                        $q->thisQuarter();
                        break;
                    case 'last_year':
                        $q->lastYear();
                        break;
                    case 'last_quarter':
                        $q->lastQuarter();
                        break;
                    case 'custom_date_range':
                        $q->dateRange(config('fi.dashboardWidgetsFromDate'), config('fi.dashboardWidgetsToDate'));
                        break;
                }
            })->sum(DB::raw('total / exchange_rate')));
    }

    private function getQuoteTotalApproved()
    {
        return CurrencyFormatter::format(QuoteAmount::join('quotes', 'quotes.id', '=', 'quote_amounts.quote_id')
            ->whereHas('quote', function ($q)
            {
                $q->approved();
                // Approval status has no bearing on whether or not a quote has been invoiced. 
                // $q->where('invoice_id', 0);
                switch (config('fi.dashboardWidgetsDateOption'))
                {
                    case 'year_to_date':
                        $q->thisYear();
                        break;
                    case 'this_month':
                        $q->thisMonth();
                        break;
                    case 'last_month':
                        $q->lastMonth();
                        break;
                    case 'this_quarter':
                        $q->thisQuarter();
                        break;
                    case 'last_year':
                        $q->lastYear();
                        break;
                    case 'last_quarter':
                        $q->lastQuarter();
                        break;
                    case 'custom_date_range':
                        $q->dateRange(config('fi.dashboardWidgetsFromDate'), config('fi.dashboardWidgetsToDate'));
                        break;
                }
            })->sum(DB::raw('total / exchange_rate')));
    }

    private function getQuoteTotalRejected()
    {
        return CurrencyFormatter::format(QuoteAmount::join('quotes', 'quotes.id', '=', 'quote_amounts.quote_id')
            ->whereHas('quote', function ($q)
            {
                $q->rejected();
                $q->where('invoice_id', 0);
                switch (config('fi.dashboardWidgetsDateOption'))
                {
                    case 'year_to_date':
                        $q->thisYear();
                        break;
                    case 'this_month':
                        $q->thisMonth();
                        break;
                    case 'last_month':
                        $q->lastMonth();
                        break;
                    case 'last_year':
                        $q->lastYear();
                            break;    
                    case 'last_quarter':
                        $q->lastQuarter();
                        break;    
                    case 'this_quarter':
                        $q->thisQuarter();
                        break;
                    case 'custom_date_range':
                        $q->dateRange(config('fi.dashboardWidgetsFromDate'), config('fi.dashboardWidgetsToDate'));
                        break;
                }
            })->sum(DB::raw('total / exchange_rate')));
    }
}