<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Modules\Reports\Controllers;

use FI\Http\Controllers\Controller;
use FI\Modules\Mru\Events\MruLog;
use FI\Modules\Reports\Reports\ProfitLossReport;
use FI\Modules\Reports\Requests\DateRangeRequest;
use FI\Support\PDF\PDFFactory;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProfitLossReportController extends Controller
{
    private $report;

    public function __construct(ProfitLossReport $report)
    {
        $this->report = $report;
    }

    public function index()
    {
        return view('reports.options.profit_loss');
    }

    public function validateOptions(DateRangeRequest $request)
    {

    }

    public function html()
    {
        event(new MruLog(['module' => 'reports', 'action' => 'profit_and_loss', 'title' => trans('fi.profit_and_loss'), 'id' => null]));

        $results = $this->report->getResults(
            request('from_date'),
            request('to_date'),
            request('company_profile_id'),
            request('include_profit_based_on'),
            request('deduct_tax_from_expenses')
        );

        return view('reports.output.profit_loss')
            ->with('results', $results);
    }

    public function pdf()
    {
        event(new MruLog(['module' => 'reports', 'action' => 'profit_and_loss', 'title' => trans('fi.profit_and_loss'), 'id' => null]));

        $pdf = PDFFactory::create();

        $results = $this->report->getResults(
            request('from_date'),
            request('to_date'),
            request('company_profile_id'),
            request('include_profit_based_on'),
            request('deduct_tax_from_expenses')
        );

        try
        {
            $html = view('reports.output.profit_loss')
                ->with('results', $results)->render();

            $pdf->download($html, trans('fi.profit_and_loss') . '.pdf');
        }
        catch (Throwable $e)
        {
            Log::error($e->getMessage());
        }
    }
}