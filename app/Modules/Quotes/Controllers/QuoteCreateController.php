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
use FI\Modules\Clients\Models\Client;
use FI\Modules\CompanyProfiles\Models\CompanyProfile;
use FI\Modules\DocumentNumberSchemes\Models\DocumentNumberScheme;
use FI\Modules\Quotes\Models\Quote;
use FI\Modules\Quotes\Requests\QuoteStoreRequest;
use FI\Support\DateFormatter;

class QuoteCreateController extends Controller
{
    public function create()
    {
        return view('quotes._modal_create')
            ->with('companyProfiles', CompanyProfile::getList())
            ->with('clients', Client::getDropDownList())
            ->with('documentNumberSchemes', DocumentNumberScheme::getList());
    }

    public function store(QuoteStoreRequest $request)
    {
        $input = $request->except('client_name');

        $input['user_id'] = auth()->user()->id;

        $client = Client::firstOrCreateByUniqueName($request->input('client_name'));
        if (false === $client)
        {
            return response()->json(['errors' => [[trans('fi.no_auth_to_create_client')]]], 403);
        }
        $input['client_id'] = $client->id;

        $input['quote_date'] = DateFormatter::unformat($input['quote_date']);

        $quote = Quote::create($input);

        return response()->json(['id' => $quote->id], 200);
    }
}