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
use FI\Modules\Reports\Reports\RevenueByClientReport;
use FI\Modules\Reports\Requests\DateRangeRequest;
use FI\Support\DateFormatter;
use FI\Support\PDF\PDFFactory;
use Illuminate\Support\Facades\Log;
use Throwable;

class RevenueByClientReportController extends Controller
{
    private $report;

    public function __construct(RevenueByClientReport $report)
    {
        $this->report = $report;
    }

    public function index()
    {
        return view('reports.options.revenue_by_client');
    }

    public function validateOptions(DateRangeRequest $request)
    {

    }

    public function html()
    {
        event(new MruLog(['module' => 'reports', 'action' => 'revenue_by_client', 'title' => trans('fi.revenue_by_client'), 'id' => null]));

        $results = $this->report->getResults(request('from_date'), request('to_date'), request('company_profile_id'));

        $months = [];

        foreach (range(1, 12) as $month)
        {
            $months[$month] = DateFormatter::getMonthShortName($month);
        }

        return view('reports.output.revenue_by_client')
            ->with('results', $results)
            ->with('months', $months);
    }

    public function pdf()
    {
        event(new MruLog(['module' => 'reports', 'action' => 'revenue_by_client', 'title' => trans('fi.revenue_by_client'), 'id' => null]));

        $pdf = PDFFactory::create();
        $pdf->setPaperOrientation('landscape');

        $results = $this->report->getResults(request('year'), request('company_profile_id'));

        $months = [];

        foreach (range(1, 12) as $month)
        {
            $months[$month] = DateFormatter::getMonthShortName($month);
        }

        try
        {
            $html = view('reports.output.revenue_by_client')
                ->with('results', $results)
                ->with('months', $months)
                ->render();

            $pdf->download($html, trans('fi.revenue_by_client') . '.pdf');
        }
        catch (Throwable $e)
        {
            Log::error($e->getMessage());
        }

    }
}