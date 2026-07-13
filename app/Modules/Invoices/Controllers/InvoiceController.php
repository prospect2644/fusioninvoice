<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Modules\Invoices\Controllers;

use DB;
use FI\Http\Controllers\Controller;
use FI\Modules\CompanyProfiles\Models\CompanyProfile;
use FI\Modules\Invoices\Events\AddTransition;
use FI\Modules\Invoices\Models\Invoice;
use FI\Modules\Tags\Models\Tag;
use FI\Support\FileNames;
use FI\Support\PDF\PDFFactory;
use FI\Support\Statuses\InvoiceStatuses;
use FI\Traits\ReturnUrl;
use Illuminate\Support\Facades\Log;
use File;
use Response;
use ZipArchive;
use PDFMerger;

class InvoiceController extends Controller
{
    use ReturnUrl;

    public function index()
    {
        $this->setReturnUrl();

        $tags             = json_decode(request('tags', '')) ?? [];
        $tagsMustMatchAll = request('tagsMustMatchAll', 0);

        $invoices = Invoice::select('invoices.*',
            DB::raw("(SELECT COUNT(credit_memo.id) FROM " . DB::getTablePrefix() . "invoices as credit_memo
                        WHERE credit_memo.type = 'credit_memo' AND credit_memo.status != 'applied' AND credit_memo.client_id = " . DB::getTablePrefix() . "invoices.client_id) as count_credit_memo"
            ),
            DB::raw("(SELECT COUNT(" . DB::getTablePrefix() . "payments.id) FROM " . DB::getTablePrefix() . "payments
                        WHERE " . DB::getTablePrefix() . "payments.client_id = " . DB::getTablePrefix() . "invoices.client_id AND " . DB::getTablePrefix() . "payments.remaining_balance > 0) as count_pre_payment"
            ),
            DB::raw("(SELECT COUNT(open_invoice.id) FROM " . DB::getTablePrefix() . "invoices as open_invoice
                        WHERE open_invoice.type = 'invoice' AND open_invoice.status IN ('sent','draft') AND open_invoice.client_id = " . DB::getTablePrefix() . "invoices.client_id) as count_sent_invoices"
            )
        )
            ->join('clients', 'clients.id', '=', 'invoices.client_id')
            ->join('invoice_amounts', 'invoice_amounts.invoice_id', '=', 'invoices.id')
            ->leftJoin('invoices_custom', 'invoices_custom.invoice_id', '=', 'invoices.id')
            ->with(['client', 'activities', 'amount.invoice.currency', 'custom'])
            ->status(request('status'))
            ->keywords(request('search'))
            ->clientId(request('client'))
            ->companyProfileId(request('company_profile'))
            ->sortable(['invoice_date' => 'desc', 'LENGTH(number)' => 'desc', 'number' => 'desc'])
            ->tags($tags, $tagsMustMatchAll)
            ->paginate(config('fi.resultsPerPage'));

        return view('invoices.index')
            ->with('invoices', $invoices)
            ->with('filterStatuses', ['' => trans('fi.all_statuses')] + InvoiceStatuses::lists())
            ->with('bulkStatuses', collect(InvoiceStatuses::lists())->except('paid'))
            ->with('companyProfiles', ['' => trans('fi.all_company_profiles')] + CompanyProfile::getList())
            ->with('searchPlaceholder', trans('fi.search_invoices'))
            ->with('tags', $tags)
            ->with('tagsMustMatchAll', $tagsMustMatchAll);
    }

    public function showFilterTags()
    {
        $resultsPerPage   = 10;
        $selectedTags     = json_decode(request('tags', '[]'));
        $tagsMustMatchAll = request('tagsMustMatchAll', 0);

        $checkedTags = Tag::where('tag_entity', '=', 'sales')
            ->whereIn('id', $selectedTags)->get();
        $allTags     = Tag::where('tag_entity', '=', 'sales')
            ->whereNotIn('id', $selectedTags)
            ->paginate($resultsPerPage);

        $nextPageCount = $resultsPerPage;
        if (($allTags->total() - ($allTags->currentPage() * $resultsPerPage)) < $resultsPerPage)
        {
            $nextPageCount = $allTags->total() - ($allTags->currentPage() * $resultsPerPage);
        }

        $nextPageLink = '';
        if ($allTags->hasMorePages())
        {
            $params       = [
                'tags'             => json_encode($selectedTags),
                'tagsMustMatchAll' => $tagsMustMatchAll,
            ];
            $nextPageLink = $allTags->appends($params)->nextPageUrl();
        }

        if (request('firstLoad'))
        {
            return view('invoices._modal_filter_tags')
                ->with('selectedTags', $selectedTags)
                ->with('tagsMustMatchAll', $tagsMustMatchAll)
                ->with('nextPageLink', $nextPageLink)
                ->with('nextPageCount', $nextPageCount)
                ->with('checkedTags', $checkedTags)
                ->with('allTags', $allTags)
                ->with('hasNoTags', ((count($allTags) + count($checkedTags)) <= 0));
        }
        else
        {
            return response()->json([
                'html'          => view('invoices._filter_tags_list')
                    ->with('selectedTags', $selectedTags)
                    ->with('tagsMustMatchAll', $tagsMustMatchAll)
                    ->with('checkedTags', $checkedTags)
                    ->with('allTags', $allTags)->render(),
                'link'          => $nextPageLink,
                'nextPageCount' => $nextPageCount,
            ]);
        }
    }

    public function delete($id)
    {
        $invoice = Invoice::find($id);
        if ($invoice->payments->count() > 0)
        {
            return response()->json(['error' => trans('fi.invoice_delete_error')]);
        }
        else
        {
            if ($invoice->type == 'credit_memo')
            {
                event(new AddTransition($invoice, 'credit_memo_deleted'));
            }
            else
            {
                event(new AddTransition($invoice, 'deleted'));
            }
            $invoice->delete();
        }

    }

    public function bulkDelete()
    {
        $invoices = Invoice::whereIn('id', request('ids'))->get();
        foreach ($invoices as $invoice)
        {
            if ($invoice->type == 'credit_memo')
            {
                event(new AddTransition($invoice, 'credit_memo_deleted'));
            }
            else
            {
                event(new AddTransition($invoice, 'deleted'));
            }
        }
        Invoice::destroy(request('ids'));
    }

    public function bulkStatus()
    {
        $ids = request('ids');
        foreach ($ids as $id)
        {
            $invoice = Invoice::find($id);
            if ($invoice->status != 'paid')
            {
                $invoice->status = request('status');
                event(new AddTransition($invoice, 'status_changed', $invoice->getOriginal('status'), $invoice->status));
                $invoice->save();
            }
        }
    }

    public function bulkPdf()
    {
        $ids         = explode(',', request('ids'));
        $zipFileName = reset($ids) . '_' . end($ids) . '_invoices.zip';

        try
        {
            // Initializing PHP class
            $zip = new ZipArchive();

            $res = $zip->open(storage_path('app/public/') . $zipFileName, ZipArchive::CREATE | ZipArchive::OVERWRITE);

            if (!$res === true)
            {
                // write to log that creating zip file failed.  If app/pubic folder does not exist, this fails with $res = 5
                Log::error('There was an error creating the zip file. Zip return code: ' . $res);
            }
            else
            {
                $unlinkFiles = [];

                foreach ($ids as $id)
                {
                    // This allows us to select invoices created by other users, but would fail trying to zip them.
                    $invoice = Invoice::whereId($id)->first();
                    if ($invoice)
                    {
                        $pdf            = PDFFactory::create();
                        $invoicePdf     = FileNames::invoice($invoice);
                        $invoicePdfPath = base_path('assets/' . $invoicePdf);
                        $unlinkFiles[]  = $invoicePdfPath;
                        $pdf->save($invoice->html, $invoicePdfPath);
                        $zip->addFile($invoicePdfPath, $invoicePdf);
                    }
                    else
                    {
                        Log::error('PDF failed on Invoice id: ' . $id);
                    }
                }
                $zip->close();

                foreach ($unlinkFiles as $file)
                {
                    if (file_exists($file))
                    {
                        unlink($file);
                    }
                }

                return response()->download(storage_path('app/public/' . $zipFileName))->deleteFileAfterSend(true);
            }
        }
        catch (\Exception $e)
        {
            Log::error('Zip operation error on open: ' . $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine());
        }
    }

    public function pdf($id)
    {
        $invoice = Invoice::find($id);

        $pdf = PDFFactory::create();

        $pdf->download($invoice->html, FileNames::invoice($invoice));
    }

    public function savePdf($id)
    {
        $invoice = Invoice::find($id);

        $pdf = PDFFactory::create();

        $fileName = FileNames::invoice($invoice);

        $pdf->save($invoice->html, storage_path('app/public/') . $fileName);

        return route('invoices.print', [$fileName]);
    }

    public function printPdf($file)
    {
        return Response::make(file_get_contents(storage_path('app/public/' . $file)), 200,
            [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $file . '"',
            ]);
    }

    public function saveBulkPdf()
    {
        $ids       = explode(',', request('ids'));
        $fileNames = [];
        foreach ($ids as $id)
        {
            // This allows us to select invoices created by other users, but would fail trying to zip them.
            $invoice  = Invoice::whereId($id)->first();
            $pdf      = PDFFactory::create();
            $fileName = FileNames::invoice($invoice);
            $pdf->save($invoice->html, storage_path('app/public/') . $fileName);
            $fileNames[] = $fileName;
        }

        return route('invoices.bulk.print', [implode(',', $fileNames)]);
    }

    public function printBulkPdf($files)
    {
        $files = explode(',', $files);
        $pdf   = new PDFMerger();
        foreach ($files as $file)
        {
            $pdf->addPDF(storage_path('app/public/' . $file), 'all');
        }

        $binaryContent = $pdf->merge('string', "bulkPrint.pdf");

        return Response::make($binaryContent, 200,
            [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'inline; filename="bulkPrint.pdf"',
            ]);
    }
}
