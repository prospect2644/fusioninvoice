<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Modules\Payments\Controllers;

use Carbon\Carbon;
use FI\Http\Controllers\Controller;
use FI\Modules\Clients\Models\Client;
use FI\Modules\Currencies\Models\Currency;
use FI\Modules\CustomFields\Models\PaymentCustom;
use FI\Modules\CustomFields\Support\CustomFieldsParser;
use FI\Modules\CustomFields\Support\CustomFieldsTransformer;
use FI\Modules\Payments\Events\AddTransition;
use FI\Modules\Payments\Events\PaymentEmailed;
use FI\Modules\Invoices\Models\Invoice;
use FI\Modules\Mru\Events\MruLog;
use FI\Modules\PaymentMethods\Models\PaymentMethod;
use FI\Modules\Payments\Models\Payment;
use FI\Modules\Payments\Models\PaymentInvoice;
use FI\Modules\Payments\Requests\PaymentRequest;
use FI\Modules\Payments\Requests\CreatePaymentRequest;
use FI\Support\DateFormatter;
use FI\Support\NumberFormatter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PaymentController extends Controller
{
    public function index()
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

        return view('payments.index')
            ->with('payments', $payments)
            ->with('searchPlaceholder', trans('fi.search_payments'));
    }

    public function create()
    {
        $date       = DateFormatter::format();
        $currencies = Currency::getList();
        $invoice    = Invoice::find(request('invoice_id'));
        return view('payments._modal_enter_payment')
            ->with('currencies', $currencies)
            ->with('invoice', $invoice)
            ->with('date', $date)
            ->with('paymentMethods', PaymentMethod::getList())
            ->with('client', $invoice->client)
            ->with('customFields', CustomFieldsParser::getFields('payments'))
            ->with('redirectTo', request('redirectTo'));
    }

    public function store(PaymentRequest $request)
    {
        $input                      = $request->except('custom', 'email_payment_receipt', 'invoice_id');
        $input['user_id']           = auth()->user()->id;
        $input['remaining_balance'] = 0;

        $invoice = Invoice::find($request->get('invoice_id'));
        if ($invoice)
        {
            $input['paid_at'] = DateFormatter::unformat($input['paid_at']);
            $payment          = Payment::create($input);
            if ($payment)
            {
                $paymentInvoice                      = new PaymentInvoice();
                $paymentInvoice->payment_id          = $payment->id;
                $paymentInvoice->invoice_id          = $request->get('invoice_id');
                $paymentInvoice->invoice_amount_paid = $request->get('amount');
                $paymentInvoice->save();
                if ($request->get('email_payment_receipt') == 1)
                {
                    event(new PaymentEmailed($payment));
                }
            }
            // Save the custom fields.
            $customFieldData = CustomFieldsTransformer::transform(request('custom', []), 'payments', $payment);
            $payment->custom->update($customFieldData);
        }
        return response()->json(['success' => true], 200);
    }

    public function createPayment()
    {
        $currencies = Currency::getList();
        return view('payments._modal_create_payment')
            ->with('currencies', $currencies)
            ->with('clients', Client::getClientListWithId())
            ->with('paymentOptions', ['for_invoices' => trans('fi.apply_payment_to_one_or_more_invoice'), 'pre_payment' => trans('fi.pre_payment_for_future_invoices')])
            ->with('paymentMethods', PaymentMethod::getList())
            ->with('payment', null)
            ->with('currency', null);
    }

    public function storePayment(CreatePaymentRequest $request)
    {
        try
        {
            $input    = $request->only(['client_id', 'payment_method_id', 'paid_at', 'note', 'amount', 'remaining_balance', 'currency_code']);
            $currency = Currency::getByCode($input['currency_code']);
            if (isset($input['remaining_balance']))
            {
                $input['remaining_balance'] = NumberFormatter::unformat($input['remaining_balance'], $currency);
            }
            $intent = $request->get('payment_intent');
            $id     = $request->get('id');
            if ($intent == 'pre_payment')
            {
                $input['remaining_balance'] = $input['amount'];
                $input['type']              = 'pre-payment';
            }
            $input['paid_at'] = DateFormatter::unformat($input['paid_at']);
            $input['user_id'] = auth()->user()->id;

            $payment = Payment::updateOrCreate(['id' => $id], $input);

            if ($intent == 'for_invoices')
            {
                $invoiceSelection = $request->input('invoice_selection');
                $paidAmount       = $request->input('paid_amount');

                foreach ($invoiceSelection as $invoiceId => $invoiceValue)
                {
                    if ($paidAmount[$invoiceId])
                    {
                        $input                        = [];
                        $input['payment_id']          = $payment->id;
                        $input['invoice_id']          = $invoiceId;
                        $input['invoice_amount_paid'] = NumberFormatter::unformat($paidAmount[$invoiceId]);
                        PaymentInvoice::create($input);
                    }
                }

            }

            if ($request->get('email_payment_receipt') == 1)
            {
                event(new PaymentEmailed($payment));
            }

            if ($id)
            {
                event(new AddTransition($payment, 'payment_updated'));
            }
            else
            {
                event(new AddTransition($payment, 'prepayment_created'));
            }
            return response()->json(['success' => true, 'message' => trans('fi.record_successfully_updated')], 200);
        }
        catch (\Exception $e)
        {
            return response()->json(['success' => false, 'message' => [[$e->getMessage()]]], 422);
        }
        return response()->json(['success' => true, 'message' => trans('fi.record_successfully_updated')], 200);

    }

    public function editPayment(Payment $payment)
    {
        $currencies = Currency::getList();
        $currency   = null;
        if ($payment)
        {
            $currency = Currency::getByCode($payment->currency_code);
        }
        return view('payments._modal_create_payment')
            ->with('currencies', $currencies)
            ->with('clients', Client::getClientListWithId())
            ->with('paymentOptions', ['for_invoices' => trans('fi.apply_payment_to_one_or_more_invoice'), 'pre_payment' => trans('fi.pre_payment_for_future_invoices')])
            ->with('paymentMethods', PaymentMethod::getList())
            ->with('payment', $payment)
            ->with('currency', $currency);
    }

    public function edit($id)
    {
        $payment = Payment::find($id);

        event(new MruLog(['module' => 'payments', 'action' => 'edit', 'id' => $id, 'title' => $payment->invoice->number . ' ' . $payment->invoice->client->name]));

        return view('payments.form')
            ->with('editMode', true)
            ->with('payment', $payment)
            ->with('paymentMethods', PaymentMethod::getList())
            ->with('invoice', $payment->invoice)
            ->with('customFields', CustomFieldsParser::getFields('payments'));
    }

    public function update(PaymentRequest $request, $id)
    {
        $input = $request->except('custom', 'referer');

        $input['paid_at'] = DateFormatter::unformat($input['paid_at']);

        $payment = Payment::find($id);
        $payment->fill($input);
        $payment->save();

        // Save the custom fields.
        $customFieldData = CustomFieldsTransformer::transform(request('custom', []), 'payments', $payment);
        $payment->custom->update($customFieldData);

        $referer = $request->get('referer', '');

        if (str_contains($referer, 'clients'))
        {
            $referer = urlSegments($referer);
            return redirect()->route('clients.show', [end($referer)])
                ->with('alertSuccess', trans('fi.record_successfully_updated'));
        }

        return redirect()->route('payments.index')
            ->with('alertSuccess', trans('fi.record_successfully_updated'));
    }

    public function delete($id)
    {
        $payment = Payment::find($id);
        event(new AddTransition($payment, 'deleted'));
        $payment->delete();

        return redirect()->route('payments.index')
            ->with('alert', trans('fi.record_successfully_deleted'));
    }

    public function bulkDelete()
    {
        Payment::destroy(request('ids'));
    }

    public function deleteImage($id, $columnName)
    {
        $customFields = PaymentCustom::wherePaymentId($id)->first();

        $existingFile = 'payments' . DIRECTORY_SEPARATOR . $customFields->{$columnName};
        if (Storage::disk(CustomFieldsTransformer::STORAGE_DISK_NAME)->exists($existingFile))
        {
            try
            {
                Storage::disk(CustomFieldsTransformer::STORAGE_DISK_NAME)->delete($existingFile);
                $customFields->{$columnName} = null;
                $customFields->save();
            }
            catch (Exception $e)
            {

            }
        }
    }

    public function editInvoicePayment($invoiceId, $id)
    {
        $payment = PaymentInvoice::whereInvoiceId($invoiceId)->whereId($id)->first();

        event(new MruLog(['module' => 'payments', 'action' => 'edit', 'id' => $id, 'title' => $payment->invoice->number . ' ' . $payment->invoice->client->name]));

        return view('invoices._modal_payment')
            ->with('editMode', true)
            ->with('payment', $payment)
            ->with('paymentMethods', PaymentMethod::getList())
            ->with('invoice', $payment->invoice)
            ->with('customFields', CustomFieldsParser::getFields('payments'))
            ->with('submitRoute', route('invoices.payments.update', [$payment->invoice_id, $payment->id]));
    }

    public function updateInvoicePayment(PaymentRequest $request, $invoiceId, $id)
    {
        $input = $request->except('custom');

        $input['paid_at'] = DateFormatter::unformat($input['paid_at']);

        $payment = Payment::find($id);
        $payment->fill($input);
        $payment->save();

        // Save the custom fields.
        $customFieldData = CustomFieldsTransformer::transform(request('custom', []), 'payments', $payment);
        $payment->custom->update($customFieldData);

        return $this->loadPayments($invoiceId);
    }

    public function deleteInvoicePayment()
    {
        try
        {
            $paymentInvoice = PaymentInvoice::find(request('id'));
            $payment        = $paymentInvoice->payment;
            if (count($payment->paymentInvoice) == 1 && $payment->type == 'single' && $payment->remaining_balance == 0)
            {
                Payment::destroy($payment->id);
            }
            else
            {
                PaymentInvoice::destroy(request('id'));
            }
            return response()->json(['success' => true, 'message' => trans('fi.payment_applied')], 200);
        }
        catch (\Exception $e)
        {
            return response()->json(['success' => false, 'message' => trans('fi.error_applying_payment')], 200);
        }
    }

    public function capturePaymentDetail(CreatePaymentRequest $request)
    {
        $request->flash();
        return response()->json(['success' => true], 200);
    }

    public function fetchInvoicesList(Request $request)
    {
        $currency_code = $request->old('currency_code');
        $currency      = Currency::getByCode($currency_code);
        if ($currency)
        {
            $client_id = $request->old('client_id');
            $client    = Client::find($client_id);
            $invoices  = Invoice::select('invoices.*')
                ->join('invoice_amounts', 'invoice_amounts.invoice_id', '=', 'invoices.id')
                ->leftJoin('invoices_custom', 'invoices_custom.invoice_id', '=', 'invoices.id')
                ->statusIn(['sent', 'draft'])
                ->clientId($client_id)
                ->type('invoice')
                ->sortable(['invoice_date' => 'desc', 'LENGTH(number)' => 'desc', 'number' => 'desc'])
                ->get();

            $request->session()->reflash();

            return view('payments._modal_fetch_invoices')
                ->with('invoices', $invoices)
                ->with('clientName', $client->name)
                ->with('currency_code', $currency->code)
                ->with('amount', $request->old('amount'))
                ->with('formatted_amount', NumberFormatter::format($request->old('amount'), $currency));
        }
    }

    public function prepareCreditApplication(Request $request, Invoice $creditMemo)
    {
        $client   = $creditMemo->client;
        $invoices = Invoice::select('invoices.*')
            ->type('invoice')
            ->join('invoice_amounts', 'invoice_amounts.invoice_id', '=', 'invoices.id')
            ->leftJoin('invoices_custom', 'invoices_custom.invoice_id', '=', 'invoices.id')
            ->statusIn(['sent', 'draft'])
            ->clientId($client->id)
            ->sortable(['invoice_date' => 'desc', 'LENGTH(number)' => 'desc', 'number' => 'desc'])
            ->get();
        $currency = Currency::getByCode($creditMemo->currency_code);
        return view('payments._modal_credit_application')
            ->with('invoices', $invoices)
            ->with('creditMemo', $creditMemo)
            ->with('clientName', $client->name)
            ->with('redirectTo', $request->get('redirect_to'))
            ->with('amount', (abs($creditMemo->amount->balance)))
            ->with('formatted_amount', NumberFormatter::format($creditMemo->amount->balance, $currency));
    }

    public function storeCreditApplication(Request $request)
    {
        try
        {
            $creditMemo = Invoice::find($request->get('credit_memo_id'));

            $payment_entry = [
                'client_id'         => $creditMemo->client->id,
                'payment_method_id' => null,
                'paid_at'           => Carbon::now()->toDateString(),
                'amount'            => $request->get('total_paid'),
                'remaining_balance' => 0,
                'credit_memo_id'    => $creditMemo->id,
                'user_id'           => auth()->user()->id,
                'type'              => 'credit-memo',
            ];
            $payment       = Payment::create($payment_entry);

            $invoiceSelection = $request->input('invoice_selection');
            $paidAmount       = $request->input('paid_amount');

            foreach ($invoiceSelection as $invoiceId => $invoiceValue)
            {
                if ($paidAmount[$invoiceId])
                {
                    $input                        = [];
                    $input['payment_id']          = $payment->id;
                    $input['invoice_id']          = $invoiceId;
                    $input['invoice_amount_paid'] = NumberFormatter::unformat($paidAmount[$invoiceId]);
                    PaymentInvoice::create($input);
                }
            }
            if (($request->get('email_payment_receipt') == 1) or ($creditMemo->client->should_email_payment_receipt))
            {
                event(new PaymentEmailed($payment));
            }

            return response()->json(['success' => true, 'message' => trans('fi.record_successfully_updated')], 200);
        }
        catch (\Exception $e)
        {
            return response()->json(['success' => false, 'message' => [[$e->getMessage()]]], 422);
        }
        return response()->json(['success' => true, 'message' => trans('fi.record_successfully_updated')], 200);

    }

    public function applications(Payment $payment)
    {
        $client          = $payment->client;
        $paymentInvoices = Invoice::select('invoices.*', 'payment_invoices.invoice_amount_paid')
            ->join('payment_invoices', 'payment_invoices.invoice_id', '=', 'invoices.id')
            ->join('invoice_amounts', 'invoice_amounts.invoice_id', '=', 'invoices.id')
            ->leftJoin('invoices_custom', 'invoices_custom.invoice_id', '=', 'invoices.id')
            ->where([
                ['payment_invoices.payment_id', '=', $payment->id],
            ])
            ->sortable(['invoice_date' => 'desc', 'LENGTH(number)' => 'desc', 'number' => 'desc'])
            ->paginate(config('fi.resultsPerPage'));

        return view('payments._modal_invoice_list')
            ->with('paymentInvoices', $paymentInvoices)
            ->with('clientName', $client->name)
            ->with('payment', $payment);
    }

    public function prepareInvoiceSettlementWithCreditMemo(Invoice $invoice)
    {
        $client      = $invoice->client;
        $settlements = $this->fetchSettlement('credit_memo', $client->id);
        return view('payments._modal_settle_invoice_creditmemo')
            ->with('settlements', $settlements)
            ->with('invoice', $invoice)
            ->with('clientName', $client->name)
            ->with('amount', $invoice->amount->balance)
            ->with('formatted_amount', $invoice->amount->formatted_numeric_balance);
    }

    public function storeInvoiceSettlementWithCreditMemo(Request $request)
    {
        try
        {
            $invoice             = Invoice::find($request->get('invoice_id'));
            $creditMemoSelection = $request->input('creditmemo_selection');
            $paidAmount          = $request->input('paid_amount');

            foreach ($creditMemoSelection as $creditMemoId => $creditMemoValue)
            {
                if ($paidAmount[$creditMemoId])
                {
                    $payment_entry = [
                        'client_id'         => $invoice->client->id,
                        'payment_method_id' => null,
                        'paid_at'           => Carbon::now()->toDateString(),
                        'amount'            => NumberFormatter::unformat($paidAmount[$creditMemoId]),
                        'remaining_balance' => 0,
                        'credit_memo_id'    => $creditMemoId,
                        'user_id'           => auth()->user()->id,
                        'type'              => 'credit-memo',
                    ];
                    $payment       = Payment::create($payment_entry);

                    $input                        = [];
                    $input['payment_id']          = $payment->id;
                    $input['invoice_id']          = $invoice->id;
                    $input['invoice_amount_paid'] = NumberFormatter::unformat($paidAmount[$creditMemoId]);
                    PaymentInvoice::create($input);

                }
            }

            return response()->json(['success' => true, 'message' => trans('fi.record_successfully_updated')], 200);
        }
        catch (\Exception $e)
        {
            return response()->json(['success' => false, 'message' => [[$e->getMessage()]]], 422);
        }
        return response()->json(['success' => true, 'message' => trans('fi.record_successfully_updated')], 200);
    }

    public function prepareInvoiceSettlementWithPrePayment(Invoice $invoice)
    {
        $client      = $invoice->client;
        $settlements = $this->fetchSettlement('pre_payment', $client->id);
        return view('payments._modal_settle_invoice_prepayment')
            ->with('settlements', $settlements)
            ->with('invoice', $invoice)
            ->with('clientName', $client->name)
            ->with('amount', $invoice->amount->balance)
            ->with('formatted_amount', $invoice->amount->formatted_numeric_balance);
    }

    public function storeInvoiceSettlementWithPrePayment(Request $request)
    {
        try
        {
            $invoice             = Invoice::find($request->get('invoice_id'));
            $prePaymentSelection = $request->input('prepayment_selection');
            $paidAmount          = $request->input('paid_amount');

            foreach ($prePaymentSelection as $prePaymentId => $prePaymentValue)
            {
                if ($paidAmount[$prePaymentId])
                {
                    $payment                    = Payment::find($prePaymentId);
                    $payment->remaining_balance = ($payment->remaining_balance - NumberFormatter::unformat($paidAmount[$prePaymentId]));
                    $payment->save();

                    $input                        = [];
                    $input['payment_id']          = $prePaymentId;
                    $input['invoice_id']          = $invoice->id;
                    $input['invoice_amount_paid'] = NumberFormatter::unformat($paidAmount[$prePaymentId]);
                    PaymentInvoice::create($input);

                }
            }

            return response()->json(['success' => true, 'message' => trans('fi.record_successfully_updated')], 200);
        }
        catch (\Exception $e)
        {
            return response()->json(['success' => false, 'message' => [[$e->getMessage()]]], 422);
        }
        return response()->json(['success' => true, 'message' => trans('fi.record_successfully_updated')], 200);
    }

    public function fetchSettlement($settlement_type, $client_id)
    {
        if ($settlement_type == 'credit_memo')
        {
            return $invoices = Invoice::select('invoices.*')
                ->where([
                    ['type', '=', 'credit_memo'],
                    ['status', '!=', 'applied'],
                ])
                ->join('invoice_amounts', 'invoice_amounts.invoice_id', '=', 'invoices.id')
                ->leftJoin('invoices_custom', 'invoices_custom.invoice_id', '=', 'invoices.id')
                ->clientId($client_id)
                ->sortable(['invoice_date' => 'desc', 'LENGTH(number)' => 'desc', 'number' => 'desc'])
                ->get();
        }

        if ($settlement_type == 'pre_payment')
        {
            return $prePayments = Payment::select('payments.*')
                ->clientId($client_id)
                ->where('remaining_balance', '>', 0)
                ->get();
        }
    }
}
