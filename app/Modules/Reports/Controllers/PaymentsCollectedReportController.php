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
use FI\Modules\Reports\Reports\PaymentsCollectedReport;
use FI\Modules\Reports\Requests\DateRangeRequest;
use FI\Support\PDF\PDFFactory;
use Illuminate\Support\Facades\Log;
use Throwable;

class PaymentsCollectedReportController extends Controller
{
    private $report;

    public function __construct(PaymentsCollectedReport $report)
    {
        $this->report = $report;
    }

    public function index()
    {
        return view('reports.options.payments_collected');
    }

    public function validateOptions(DateRangeRequest $request)
    {

    }

    public function html()
    {
        event(new MruLog(['module' => 'reports', 'action' => 'payments_collected', 'title' => trans('fi.payments_collected'), 'id' => null]));

        $results = $this->report->getResults(
            request('from_date'),
            request('to_date'),
            request('company_profile_id'),
            request('prepayments'),
            request('currency_format')
        );

        return view('reports.output.payments_collected')
            ->with('results', $results);
    }

    public function pdf()
    {
        event(new MruLog(['module' => 'reports', 'action' => 'payments_collected', 'title' => trans('fi.payments_collected'), 'id' => null]));

        $pdf = PDFFactory::create();
        $pdf->setPaperOrientation('landscape');

        $results = $this->report->getResults(
            request('from_date'),
            request('to_date'),
            request('company_profile_id'),
            request('prepayments'),
            request('currency_format')
        );

        try
        {
            $html = view('reports.output.payments_collected')
                ->with('results', $results)->render();

            $pdf->download($html, trans('fi.payments_collected') . '.pdf');
        }
        catch (Throwable $e)
        {
            Log::error($e->getMessage());
        }
    }
}