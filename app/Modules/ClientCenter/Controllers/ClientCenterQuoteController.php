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
use FI\Modules\Quotes\Models\Quote;
use Illuminate\Support\Facades\DB;

class ClientCenterQuoteController extends Controller
{
    public function index()
    {
        app()->setLocale(auth()->user()->client->language);
        
        $quotes = Quote::with(['amount.quote.currency', 'client'])
            ->where('client_id', auth()->user()->client->id)
            ->orderBy('created_at', 'DESC')
            ->orderBy(DB::raw('length(number)'), 'DESC')
            ->orderBy('number', 'DESC')
            ->paginate(config('fi.resultsPerPage'));

        return view('client_center.quotes.index')
            ->with('quotes', $quotes);
    }
}