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
use FI\Modules\Invoices\Models\Invoice;
use FI\Modules\Payments\Models\Payment;
use FI\Modules\Quotes\Models\Quote;
use Illuminate\Support\Facades\DB;

class ClientCenterDashboardController extends Controller
{
    public function index()
    {
        $clientId = auth()->user()->client->id;

        app()->setLocale(auth()->user()->client->language);

        $invoices = Invoice::with(['amount.invoice.currency', 'client'])
            ->where('client_id', $clientId)
            ->orderBy('created_at', 'DESC')
            ->orderBy(DB::raw('length(number)'), 'DESC')
            ->orderBy('number', 'DESC')
            ->limit(5)->get();

        $quotes = Quote::with(['amount.quote.currency', 'client'])
            ->where('client_id', $clientId)
            ->orderBy('created_at', 'DESC')
            ->orderBy(DB::raw('length(number)'), 'DESC')
            ->orderBy('number', 'DESC')
            ->limit(5)->get();

        $payments = Payment::select('payments.*')
            ->with(['paymentInvoice.invoice.client', 'paymentInvoice.invoice.currency', 'paymentMethod'])
            ->leftJoin('payment_invoices', 'payments.id', '=', 'payment_invoices.invoice_id')
            ->leftJoin('invoices', 'payment_invoices.invoice_id', '=', 'invoices.id')
            ->leftJoin('clients', 'clients.id', '=', 'invoices.client_id')
            ->leftJoin('payment_methods', 'payment_methods.id', '=', 'payments.payment_method_id')
            ->leftJoin('payments_custom', 'payments_custom.payment_id', '=', 'payments.id')
            ->clientId(auth()->user()->client->id)
            ->groupBy('payments.id')
            ->whereNull('credit_memo_id')
            ->limit(5)->get();

        return view('client_center.index')
            ->with('invoices', $invoices)
            ->with('quotes', $quotes)
            ->with('payments', $payments);
    }

    public function redirectToLogin()
    {
        return redirect()->route('session.login');
    }
}