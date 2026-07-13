@include('layouts._datepicker')
@include('payments._js_create')
<div class="modal modal-wide fade" id="modal-enter-payment" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-enter-payment modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">{{ trans('fi.enter_payment_for_invoice') }}</h4>
            </div>
            <div class="modal-body">

                <div id="modal-status-placeholder"></div>

                <form class="form-horizontal">
                    <div class="col-lg-6">
                        <input type="hidden" name="invoice_id" id="invoice_id" value="{{ $invoice->id }}">

                        <div class="form-group">
                            <label class="col-sm-4 control-label">{{ trans('fi.client') }}</label>

                            <div class="col-sm-8">
                                {!! Form::select('client_id', [$client->id=>$client->name], $client->id, ['id' => 'client_id', 'class' => 'form-control disabled', 'disabled' => true]) !!}
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-4 control-label">{{ trans('fi.invoice') }}</label>

                            <div class="col-sm-8">
                                {!! Form::select('invoice_id', [$invoice->id => $invoice->number], $invoice->id, ['id' => 'invoice_id', 'class' => 'form-control disabled', 'disabled' => true]) !!}
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-4 control-label">{{ trans('fi.invoice_balance') }}</label>

                            <div class="col-sm-8">
                                {!! Form::text('invoice_balance', $invoice->amount->formatted_balance, ['id' => 'invoice_balance', 'class' => 'form-control disabled', 'disabled' => true, 'data-amount' => $invoice->amount->balance]) !!}
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-4 control-label">{{ trans('fi.currency') }}</label>

                            <div class="col-sm-8">
                                {!! Form::select('currency_code', $currencies, $invoice->currency_code, ['disabled' => true,'id' =>
                                'currency_code', 'class' => 'form-control']) !!}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-4 control-label">{{ trans('fi.note') }}</label>

                            <div class="col-sm-8">
                                {!! Form::textarea('payment_note', null, ['id' => 'payment_note', 'class' => 'form-control', 'rows' => 4]) !!}
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label class="col-sm-4 control-label">{{ trans('fi.amount') }}</label>

                            <div class="col-sm-8">
                                {!! Form::text('payment_amount', $invoice->amount->formatted_numeric_balance, ['id' => 'payment_amount', 'class' => 'form-control currency-input-validator']) !!}
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-4 control-label">{{ trans('fi.payment_date') }}</label>

                            <div class="col-sm-8">
                                {!! Form::text('payment_date', $date, ['id' => 'payment_date', 'class' => 'form-control']) !!}
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-4 control-label">{{ trans('fi.payment_method') }}</label>

                            <div class="col-sm-8">
                                {!! Form::select('payment_method_id', $paymentMethods, null, ['id' => 'payment_method_id', 'class' => 'form-control']) !!}
                            </div>
                        </div>


                        @if (config('fi.mailConfigured') and $client->email)
                            <div class="form-group">
                                <label class="col-sm-4 control-label"
                                       for="email_payment_receipt">{{ trans('fi.email_payment_receipt') }}</label>

                                <div class="col-sm-8">
                                    {!! Form::checkbox('email_payment_receipt', 1, $client->should_email_payment_receipt, ['id' => 'email_payment_receipt']) !!}
                                </div>
                            </div>
                        @else
                            <div class="form-group">
                                <label class="col-sm-4 control-label">{{ trans('fi.email_payment_receipt') }}</label>

                                <div class="col-sm-8">
                                    {!! Form::checkbox('dummy', '', '', ['disabled' => 'true', 'title' => trans('fi.email_payment_receipt_notice')]) !!}
                                </div>
                            </div>
                        @endif

                        <div id="payment-custom-fields">
                            @if ($customFields)
                                @include('custom_fields._custom_fields_modal')
                            @endif
                        </div>
                        <div class="form-group" style="margin-bottom: 0">
                            <label class="col-sm-4 control-label">{{ trans('fi.remaining_balance') }}</label>

                            <div class="col-sm-8">
                                {!! Form::text('remaining_balance', 0.00 , ['class' => 'form-control disabled', 'id' => 'remaining_balance','readonly'=>true]) !!}
                            </div>
                        </div>
                    </div>
                </form>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('fi.cancel') }}</button>
                @can('payments.create')
                <button type="button" id="enter-payment-confirm" class="btn btn-primary"
                        data-loading-text="{{ trans('fi.please_wait') }}...">{{ trans('fi.submit') }}</button>
                @endcan
            </div>
        </div>
    </div>
</div>
