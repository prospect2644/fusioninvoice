<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Modules\Quotes\Controllers;

use FI\Http\Controllers\Controller;
use FI\Modules\CompanyProfiles\Models\CompanyProfile;
use FI\Modules\Quotes\Models\Quote;
use FI\Modules\Quotes\Events\AddTransition;
use FI\Modules\Quotes\Events\QuoteToInvoiceTransition;
use FI\Modules\Quotes\Support\QuoteToInvoice;
use FI\Support\DateFormatter;
use FI\Support\FileNames;
use FI\Support\PDF\PDFFactory;
use FI\Support\Statuses\QuoteStatuses;
use FI\Traits\ReturnUrl;
use File;
use Response;
use ZipArchive;

class QuoteController extends Controller
{
    use ReturnUrl;

    public function index()
    {
        $this->setReturnUrl();

        $quotes = Quote::select('quotes.*')
            ->join('clients', 'clients.id', '=', 'quotes.client_id')
            ->join('quote_amounts', 'quote_amounts.quote_id', '=', 'quotes.id')
            ->leftJoin('quotes_custom', 'quotes_custom.quote_id', '=', 'quotes.id')
            ->with(['client', 'activities', 'amount.quote.currency'])
            ->status(request('status'))
            ->keywords(request('search'))
            ->clientId(request('client'))
            ->companyProfileId(request('company_profile'))
            ->sortable(['quote_date' => 'desc', 'LENGTH(number)' => 'desc', 'number' => 'desc'])
            ->paginate(config('fi.resultsPerPage'));

        return view('quotes.index')
            ->with('quotes', $quotes)
            ->with('filterStatuses', ['' => trans('fi.all_statuses')] + QuoteStatuses::lists())
            ->with('bulkStatuses', QuoteStatuses::lists())
            ->with('companyProfiles', ['' => trans('fi.all_company_profiles')] + CompanyProfile::getList())
            ->with('searchPlaceholder', trans('fi.search_quotes'));
    }

    public function delete($id)
    {
        $quote = Quote::find($id);
        event(new AddTransition($quote, 'deleted'));
        Quote::destroy($id);

        return redirect()->route('quotes.index')
            ->with('alert', trans('fi.record_successfully_deleted'));
    }

    public function bulkDelete()
    {
        $quotes = Quote::whereIn('id', request('ids'))->get();
        foreach ($quotes as $quote)
        {
            event(new AddTransition($quote, 'deleted'));
        }
        Quote::destroy(request('ids'));
    }

    public function bulkStatus()
    {
        $ids = request('ids');
        foreach ($ids as $id)
        {
            $quote         = Quote::find($id);
            $quote->status = request('status');
            event(new AddTransition($quote, 'status_changed', $quote->getOriginal('status'), $quote->status));
            $quote->save();

            if ($quote->status == 'approved' && config('fi.convertQuoteWhenApproved'))
            {
                $quoteToInvoice = new QuoteToInvoice();

                $invoice = $quoteToInvoice->convert(
                    $quote,
                    date('Y-m-d'),
                    DateFormatter::incrementDateByDays(date('Y-m-d'), config('fi.invoicesDueAfter')),
                    config('fi.invoiceGroup')
                );

                event(new QuoteToInvoiceTransition($quote, $invoice));
            }
        }
    }

    public function bulkPdf()
    {
        $ids         = explode(',', request('ids'));
        $zipFileName = reset($ids) . '_' . end($ids) . '_quotes.zip';

        // Initializing PHP class
        $zip = new ZipArchive();

        $zip->open(storage_path('app/public/') . $zipFileName, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        $unlinkFiles = [];

        foreach ($ids as $id)
        {
            $quote = Quote::whereUserId(auth()->user()->id)->whereId($id)->first();
            if ($quote)
            {
                $pdf           = PDFFactory::create();
                $quotePdf      = FileNames::invoice($quote);
                $quotePdfPath  = base_path('assets/' . $quotePdf);
                $unlinkFiles[] = $quotePdfPath;
                $pdf->save($quote->html, $quotePdfPath);
                $zip->addFile($quotePdfPath, $quotePdf);
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

    public function pdf($id)
    {
        $quote = Quote::find($id);

        $pdf = PDFFactory::create();

        $pdf->download($quote->html, FileNames::quote($quote));
    }

    public function savePdf($id)
    {
        $quote = Quote::find($id);

        $pdf = PDFFactory::create();

        $fileName = FileNames::invoice($quote);

        $pdf->save($quote->html, storage_path('app/public/') . $fileName);

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
            $quote  = Quote::whereId($id)->first();
            $pdf      = PDFFactory::create();
            $fileName = FileNames::invoice($quote);
            $pdf->save($quote->html, storage_path('app/public/') . $fileName);
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