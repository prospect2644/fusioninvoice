<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Modules\API\Controllers;

use FI\Modules\API\Requests\APIPaymentCustomFieldsRequest;
use FI\Modules\CustomFields\Models\PaymentCustom;
use FI\Modules\Invoices\Models\Invoice;
use FI\Modules\Payments\Models\Payment;
use FI\Modules\Payments\Models\PaymentInvoice;
use Illuminate\Http\Request;

class ApiPaymentController extends ApiController
{
    public function index(Request $request)
    {
        try
        {
            if ($request->has('paginated_response') && $request->get('paginated_response') == 1)
            {
                $payments = Payment::select('payments.*')
                    ->with(['paymentInvoice.invoice.client', 'paymentInvoice.invoice.currency', 'paymentMethod'])
                    ->leftJoin('payment_invoices', 'payments.id', '=', 'payment_invoices.invoice_id')
                    ->leftJoin('invoices', 'payment_invoices.invoice_id', '=', 'invoices.id')
                    ->leftJoin('clients', 'clients.id', '=', 'invoices.client_id')
                    ->leftJoin('payment_methods', 'payment_methods.id', '=', 'payments.payment_method_id')
                    ->leftJoin('payments_custom', 'payments_custom.payment_id', '=', 'payments.id')
                    ->keywords(request('search'))
                    ->clientId(request('client'))
                    ->whereNull('credit_memo_id')
                    ->groupBy('payments.id')
                    ->sortable(['paid_at' => 'desc', 'length(number)' => 'desc', 'number' => 'desc'])
                    ->paginate(config('fi.resultsPerPage'));
            }
            else
            {
                $payments = Payment::select('payments.*')
                    ->with(['paymentInvoice.invoice.client', 'paymentInvoice.invoice.currency', 'paymentMethod'])
                    ->leftJoin('payment_invoices', 'payments.id', '=', 'payment_invoices.invoice_id')
                    ->leftJoin('invoices', 'payment_invoices.invoice_id', '=', 'invoices.id')
                    ->leftJoin('clients', 'clients.id', '=', 'invoices.client_id')
                    ->leftJoin('payment_methods', 'payment_methods.id', '=', 'payments.payment_method_id')
                    ->leftJoin('payments_custom', 'payments_custom.payment_id', '=', 'payments.id')
                    ->keywords(request('search'))
                    ->clientId(request('client'))
                    ->whereNull('credit_memo_id')
                    ->groupBy('payments.id')
                    ->sortable(['paid_at' => 'desc', 'length(number)' => 'desc', 'number' => 'desc'])
                    ->get();
            }

            return response()->json(['success' => true, 'message' => trans('fi.record_successfully_retrieved'), 'data' => $payments], 200);

        }
        catch (Exception $e)
        {
            return response()->json(['success' => false, 'message' => trans('fi.record_not_found')], 400);
        }

    }

    public function show($id, Request $request)
    {
        try
        {
            $payment = Payment::whereId($id)->customField($request->get('include_custom_fields'))->first();

            if ($payment)
            {
                return response()->json(['success' => true, 'message' => trans('fi.record_successfully_retrieved'), 'data' => $payment], 200);
            }
            else
            {
                return response()->json(['success' => false, 'message' => trans('fi.record_not_found')], 400);
            }

        }
        catch (Exception $e)
        {
            return response()->json(['success' => false, 'message' => trans('fi.record_not_found')], 400);
        }

    }

    public function store(Request $request)
    {
        try
        {
            $input            = $request->except('invoice_id');
            $input['user_id'] = auth()->user()->id;

            $invoice = Invoice::whereId($request->get('invoice_id'))->whereClientId($request->get('client_id'))->first();

            if ($invoice)
            {
                $payment = Payment::create($input);
                if ($payment)
                {
                    $paymentInvoice                      = new PaymentInvoice();
                    $paymentInvoice->payment_id          = $payment->id;
                    $paymentInvoice->invoice_id          = $request->get('invoice_id');
                    $paymentInvoice->invoice_amount_paid = $request->get('amount');
                    $paymentInvoice->save();
                }
                $paymentDetail = Payment::select('payments.*')->where('payments.id', $payment->id)->with(['paymentInvoice.invoice.client', 'paymentInvoice.invoice.currency', 'paymentMethod'])
                    ->leftJoin('payment_invoices', 'payments.id', '=', 'payment_invoices.invoice_id')
                    ->leftJoin('invoices', 'payment_invoices.invoice_id', '=', 'invoices.id')
                    ->leftJoin('clients', 'clients.id', '=', 'invoices.client_id')
                    ->leftJoin('payment_methods', 'payment_methods.id', '=', 'payments.payment_method_id')
                    ->leftJoin('payments_custom', 'payments_custom.payment_id', '=', 'payments.id')
                    ->first();

                return response()->json(['success' => true, 'message' => trans('fi.record_successfully_created'), 'data' => $paymentDetail], 201);
            }
            else
            {
                return response()->json(['success' => false, 'message' => trans('fi.invalid_invoice_id')], 400);
            }

        }
        catch (Exception $e)
        {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function delete($id)
    {
        try
        {

            if (Payment::destroy($id) == true)
            {
                return response()->json(['success' => true, 'message' => trans('fi.record_successfully_deleted')], 200);
            }
            else
            {
                return response()->json(['success' => false, 'message' => trans('fi.record_not_found')], 400);
            }

        }
        catch (Exception $e)
        {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }

    }

    public function addUpdateCustomFields(APIPaymentCustomFieldsRequest $request)
    {
        try
        {

            $input = $request->except('payment_id');

            PaymentCustom::updateOrCreate(['payment_id' => request('payment_id')], $input);

            $payment = Payment::with('custom')->find(request('payment_id'));

            return response()->json(['success' => true, 'message' => trans('fi.record_successfully_created'), 'data' => $payment], 200);

        }
        catch (Exception $e)
        {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }

    }
}