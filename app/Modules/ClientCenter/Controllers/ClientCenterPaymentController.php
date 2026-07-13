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
use FI\Modules\Payments\Models\Payment;

class ClientCenterPaymentController extends Controller
{
    public function index()
    {
        app()->setLocale(auth()->user()->client->language);

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
                    ->paginate(config('fi.resultsPerPage'));

        return view('client_center.payments.index')
            ->with('payments', $payments);
    }
}