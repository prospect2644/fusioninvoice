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
use FI\Modules\Expenses\Models\ExpenseCategory;
use FI\Modules\Expenses\Models\ExpenseVendor;
use FI\Modules\Mru\Events\MruLog;
use FI\Modules\Reports\Reports\ExpenseListReport;
use FI\Modules\Reports\Requests\DateRangeRequest;
use FI\Support\PDF\PDFFactory;
use Illuminate\Support\Facades\Log;
use Throwable;

class ExpenseListReportController extends Controller
{
    private $report;

    public function __construct(ExpenseListReport $report)
    {
        $this->report = $report;
    }

    public function index()
    {
        return view('reports.options.expense_list')
            ->with('categories', ['' => trans('fi.all_categories')] + ExpenseCategory::getList())
            ->with('vendors', ['' => trans('fi.all_vendors')] + ExpenseVendor::getList());
    }

    public function validateOptions(DateRangeRequest $request)
    {

    }

    public function html()
    {
        event(new MruLog(['module' => 'reports', 'action' => 'expense_list', 'title' => trans('fi.expense_list'), 'id' => null]));

        $results = $this->report->getResults(
            request('from_date'),
            request('to_date'),
            request('company_profile_id'),
            request('category_id'),
            request('vendor_id')
        );

        return view('reports.output.expense_list')
            ->with('results', $results);
    }

    public function pdf()
    {
        event(new MruLog(['module' => 'reports', 'action' => 'expense_list', 'title' => trans('fi.expense_list'), 'id' => null]));

        $pdf = PDFFactory::create();
        $pdf->setPaperOrientation('landscape');

        $results = $this->report->getResults(
            request('from_date'),
            request('to_date'),
            request('company_profile_id'),
            request('category_id'),
            request('vendor_id')
        );

        try
        {
            $html = view('reports.output.expense_list')
                ->with('results', $results)->render();

            $pdf->download($html, trans('fi.expense_list') . '.pdf');
        }
        catch (Throwable $e)
        {
            Log::error($e->getMessage());
        }
    }
}