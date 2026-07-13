<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Modules\ClientCenter\Controllers;

use FI\Http\Controllers\Controller;
use FI\Modules\Quotes\Events\AddTransition;
use FI\Modules\Quotes\Events\QuoteApproved;
use FI\Modules\Quotes\Events\QuoteRejected;
use FI\Modules\Quotes\Events\QuoteViewed;
use FI\Modules\Quotes\Models\Quote;
use FI\Modules\Users\Models\User;
use FI\Support\FileNames;
use FI\Support\PDF\PDFFactory;

class ClientCenterPublicQuoteController extends Controller
{
    public function show($urlKey)
    {
        $quote = Quote::where('url_key', $urlKey)->first();

        app()->setLocale($quote->client->language);

        $userId = User::whereUserType('system')->first()->id;

        event(new QuoteViewed($quote));
        event(new AddTransition($quote, 'email_opened', '', '', $userId));

        return view('client_center.quotes.public')
            ->with('quote', $quote)
            ->with('urlKey', $urlKey)
            ->with('attachments', $quote->clientAttachments);
    }

    public function pdf($urlKey)
    {
        $quote = Quote::with('items.taxRate', 'items.taxRate2', 'items.amount.item.quote', 'items.quote')
            ->where('url_key', $urlKey)->first();

        event(new QuoteViewed($quote));

        $pdf = PDFFactory::create();

        $pdf->download($quote->html, FileNames::quote($quote));
    }

    public function html($urlKey)
    {
        $quote = Quote::with('items.taxRate', 'items.taxRate2', 'items.amount.item.quote', 'items.quote')
            ->where('url_key', $urlKey)->first();

        return $quote->html;
    }

    public function approve($urlKey)
    {
        $quote = Quote::where('url_key', $urlKey)->first();

        $quote->status = 'approved';

        $quote->save();

        event(new QuoteApproved($quote));

        return redirect()->route('clientCenter.public.quote.show', [$urlKey]);
    }

    public function reject($urlKey)
    {
        $quote = Quote::where('url_key', $urlKey)->first();

        $quote->status = 'rejected';

        $quote->save();

        event(new QuoteRejected($quote));

        return redirect()->route('clientCenter.public.quote.show', [$urlKey]);
    }
}
