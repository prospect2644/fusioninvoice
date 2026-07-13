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
use FI\Modules\ItemLookups\Models\ItemCategory;
use FI\Modules\Mru\Events\MruLog;
use FI\Modules\Reports\Reports\ItemSalesReport;
use FI\Modules\Reports\Requests\DateRangeRequest;
use FI\Support\PDF\PDFFactory;
use Illuminate\Support\Facades\Log;
use Throwable;

class ItemSalesReportController extends Controller
{
    private $report;

    public function __construct(ItemSalesReport $report)
    {
        $this->report = $report;
    }

    public function index()
    {
        return view('reports.options.item_sales')
            ->with('categories', ['' => trans('fi.all_categories')] + ItemCategory::getList());
    }

    public function validateOptions(DateRangeRequest $request)
    {

    }

    public function html()
    {
        event(new MruLog(['module' => 'reports', 'action' => 'item_sales', 'title' => trans('fi.item_sales'), 'id' => null]));

        $results = $this->report->getResults(
            request('from_date'),
            request('to_date'),
            request('company_profile_id'),
            request('category_id')
        );

        return view('reports.output.item_sales')
            ->with('results', $results);
    }

    public function pdf()
    {
        event(new MruLog(['module' => 'reports', 'action' => 'item_sales', 'title' => trans('fi.item_sales'), 'id' => null]));

        $pdf = PDFFactory::create();

        $results = $this->report->getResults(
            request('from_date'),
            request('to_date'),
            request('company_profile_id'),
            request('category_id')
        );

        try
        {
            $html = view('reports.output.item_sales')
                ->with('results', $results)->render();

            $pdf->download($html, trans('fi.item_sales') . '.pdf');
        }
        catch (Throwable $e)
        {
            Log::error($e->getMessage());
        }
    }
}