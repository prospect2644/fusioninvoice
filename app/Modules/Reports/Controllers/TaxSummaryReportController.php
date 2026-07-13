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
use FI\Modules\Reports\Reports\TaxSummaryReport;
use FI\Modules\Reports\Requests\DateRangeRequest;
use FI\Support\PDF\PDFFactory;
use Illuminate\Support\Facades\Log;
use Throwable;

class TaxSummaryReportController extends Controller
{
    private $report;

    public function __construct(TaxSummaryReport $report)
    {
        $this->report = $report;
    }

    public function index()
    {
        return view('reports.options.tax_summary');
    }

    public function validateOptions(DateRangeRequest $request)
    {

    }

    public function html()
    {
        event(new MruLog(['module' => 'reports', 'action' => 'tax_summary', 'title' => trans('fi.tax_summary'), 'id' => null]));

        $results = $this->report->getResults(
            request('from_date'),
            request('to_date'),
            request('company_profile_id'),
            request('exclude_unpaid_invoices')
        );

        return view('reports.output.tax_summary')
            ->with('results', $results);
    }

    public function pdf()
    {
        event(new MruLog(['module' => 'reports', 'action' => 'tax_summary', 'title' => trans('fi.tax_summary'), 'id' => null]));

        $pdf = PDFFactory::create();

        $results = $this->report->getResults(
            request('from_date'),
            request('to_date'),
            request('company_profile_id'),
            request('exclude_unpaid_invoices')
        );

        try
        {
            $html = view('reports.output.tax_summary')
                ->with('results', $results)->render();

            $pdf->download($html, trans('fi.tax_summary') . '.pdf');
        }
        catch (Throwable $e)
        {
            Log::error($e->getMessage());
        }


    }
}