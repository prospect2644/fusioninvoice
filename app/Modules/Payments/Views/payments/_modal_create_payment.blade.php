@include('layouts._datepicker')
@include('payments._js_create_payment')

<div class="modal fade" id="create-payment" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                @if($payment)
                    <h4 class="modal-title">{{ trans('fi.edit_payment') }}</h4>
                @else
                    <h4 class="modal-title">{{ trans('fi.enter_payment') }}</h4>
                @endif
            </div>
            <div class="modal-body">

                <div id="modal-status-placeholder"></div>
                @php
                $id = ($payment) ? $payment->id : null;
                $paymentIntent = (old('payment_intent')) ? old('payment_intent') : (($payment) ? 'pre_payment' :
                'for_invoices');
                $clientId = (old('client_id')) ? old('client_id') : (($payment) ? $payment->client_id : null);
                $amount = (old('amount')) ? old('amount') : (($payment) ? sprintf("%.2f", $payment->amount) : null);
                $paidAt = (old('paid_at')) ? old('paid_at') : (($payment) ? $payment->formatted_paid_at : null);
                $paymentMethodId = (old('payment_method_id')) ? old('payment_method_id') : (($payment) ?
                $payment->payment_method_id : null);
                $note = (old('note')) ? old('note') : (($payment) ? $payment->note : null);
                $btnText = ($payment) ? trans('fi.save') : trans('fi.apply_to_invoices');
                @endphp
                <form class="form-horizontal" id="create-payment-form">
                    {!! Form::hidden('id', $id) !!}
                    <div class="form-group">
                        <label class="col-sm-4 control-label">{{ trans('fi.payment_intent') }}</label>

                        <div class="col-sm-8">
                            {!! Form::select('payment_intent', $paymentOptions, $paymentIntent, ['id' => 'payment_intent', 'class' => 'form-control']) !!}
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-4 control-label">{{ trans('fi.client') }}</label>

                        <div class="col-sm-8">
                            {!! Form::select('client_id', $clients, $clientId, ['id' => 'client_id', 'class' => 'form-control client-lookup', 'autocomplete' => 'off']) !!}
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-4 control-label">{{ trans('fi.currency') }}</label>

                        <div class="col-sm-8">
                            {!! Form::select('currency_code', $currencies, ($payment) ? $payment->currency_code : config('fi.baseCurrency'), ['id' => 'currency_code', 'class' => 'form-control']) !!}
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-4 control-label">{{ trans('fi.payment_amount') }}</label>

                        <div class="col-sm-8">
                            {!! Form::text('amount', (!empty($payment) && !empty($currency)) ? \FI\Support\NumberFormatter::format($payment->amount, $currency) : "", ['id' => 'amount', 'data-amount' => $amount,'class' => 'form-control', 'autocomplete' => 'off']) !!}
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-4 control-label">{{ trans('fi.payment_date') }}</label>

                        <div class="col-sm-8">
                            {!! Form::text('paid_at', $paidAt, ['id' => 'paid_at', 'class'=> 'form-control', 'autocomplete' => 'off']) !!}
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-4 control-label">{{ trans('fi.payment_method') }}</label>

                        <div class="col-sm-8">
                            {!! Form::select('payment_method_id', $paymentMethods, $paymentMethodId, ['id' => 'payment_method_id', 'class' => 'form-control']) !!}
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-4 control-label">{{ trans('fi.note') }}</label>

                        <div class="col-sm-8">
                            {!! Form::textarea('note', $note, ['id' => 'note', 'class' => 'form-control', 'rows' => 4]) !!}
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-4 control-label">{{ trans('fi.email_payment_receipt') }}</label>

                        <div class="col-sm-8">
                            {!! Form::checkbox('email_payment_receipt', 1, null, ['id' => 'email_payment_receipt', 'class' => 'check check-aligned']) !!}
                        </div>
                    </div>
                </form>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('fi.cancel') }}</button>
                @can('payments.create')
                <button type="button" id="create-payment-confirm" class="btn btn-primary" data-text="{{ $btnText }}"
                        data-loading-text="{{ trans('fi.please_wait') }}..."> {{ $btnText }} </button>
                @endcan
            </div>
        </div>
    </div>
</div>
