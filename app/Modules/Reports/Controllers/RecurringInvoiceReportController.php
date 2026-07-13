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
use FI\Modules\Reports\Reports\RecurringInvoiceReport;
use FI\Modules\Reports\Requests\DateRangeRequest;
use FI\Support\PDF\PDFFactory;
use Illuminate\Support\Facades\Log;
use Throwable;

class RecurringInvoiceReportController extends Controller
{
    private $report;

    public function __construct(RecurringInvoiceReport $report)
    {
        $this->report = $report;
    }

    public function index()
    {
        return view('reports.options.recurring_invoice');
    }

    public function validateOptions(DateRangeRequest $request)
    {

    }

    public function html()
    {
        event(new MruLog(['module' => 'reports', 'action' => 'recurring_invoice_list', 'title' => trans('fi.recurring_invoice_list'), 'id' => null]));

        $results = $this->report->getResults(
            request('from_date'),
            request('to_date'),
            request('company_profile_id')
        );

        return view('reports.output.recurring_invoice')
            ->with('results', $results);
    }

    public function pdf()
    {
        event(new MruLog(['module' => 'reports', 'action' => 'recurring_invoice_list', 'title' => trans('fi.recurring_invoice_list'), 'id' => null]));

        $pdf = PDFFactory::create();

        $results = $this->report->getResults(
            request('from_date'),
            request('to_date'),
            request('company_profile_id')
        );

        try
        {
            $html = view('reports.output.recurring_invoice')
                ->with('results', $results)->render();

            $pdf->download($html, trans('fi.recurring_invoice') . '.pdf');
        }
        catch (Throwable $e)
        {
            Log::error($e->getMessage());
        }
    }
}