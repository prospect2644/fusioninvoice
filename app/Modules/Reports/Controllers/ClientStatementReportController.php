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
use FI\Modules\Clients\Models\Client;
use FI\Modules\Mru\Events\MruLog;
use FI\Modules\Reports\Reports\ClientStatementReport;
use FI\Modules\Reports\Requests\ClientStatementReportRequest;
use FI\Support\PDF\PDFFactory;
use Illuminate\Support\Facades\Log;
use Throwable;

class ClientStatementReportController extends Controller
{
    private $report;

    public function __construct(ClientStatementReport $report)
    {
        $this->report = $report;
    }

    public function index()
    {
        return view('reports.options.client_statement')
            ->with('clients', ['' => trans('fi.all_client')] + Client::getList());
    }

    public function validateOptions(ClientStatementReportRequest $request)
    {

    }

    public function html()
    {
        event(new MruLog(['module' => 'reports', 'action' => 'client_statement', 'title' => trans('fi.client_statement'), 'id' => null]));

        $results = $this->report->getResults(
            request('client_name'),
            request('from_date'),
            request('to_date'),
            request('company_profile_id'));

        return view('reports.output.client_statement')
            ->with('results', $results);
    }

    public function pdf()
    {
        event(new MruLog(['module' => 'reports', 'action' => 'client_statement', 'title' => trans('fi.client_statement'), 'id' => null]));

        $pdf = PDFFactory::create();
        $pdf->setPaperOrientation('landscape');

        $results = $this->report->getResults(
            request('client_name'),
            request('from_date'),
            request('to_date'),
            request('company_profile_id'));

        try
        {
            $html = view('reports.output.client_statement')
                ->with('results', $results)->render();

            $pdf->download($html, trans('fi.client_statement') . '.pdf');
        }
        catch (Throwable $e)
        {
            Log::error($e->getMessage());
        }

    }
}