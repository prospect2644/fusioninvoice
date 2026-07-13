@include('layouts._datepicker')
@include('payments._js_settle_invoice_prepayment')
<div class="modal fade modal-wide" id="modal-fetch-invoices" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">{{ trans('fi.prepayment_applications_for_invoice') }}
                    #{{ $invoice->number }}</h4>
            </div>
            <div class="modal-body">

                <div id="modal-status-placeholder"></div>

                <form class="form-inline" id="fetch-invoices-form">
                    {!! Form::hidden('invoice_id', $invoice->id) !!}
                    {!! Form::hidden('total_paid', 0, ['id' => 'total_paid']) !!}
                    <div class="form-group" style="margin-left: 7px;">
                        <label class="control-label">{{ trans('fi.client_name') }}:</label>
                        {!! Form::text('client_name', $clientName , ['class' => 'form-control disabled', 'id' => 'client_name','disabled'=>true, 'size' => 50]) !!}
                    </div>
                    <div class="form-group" style="margin-left: 10px;">
                        <label class="control-label">{{ trans('fi.invoice_amount') }}:</label>

                        <div class="input-group">
                            <span class="input-group-addon"><i
                                        class="fa {{getCurrencyClass($invoice->currency_code)}}"></i></span>
                            <input type="text" name="amount" value="{{$formatted_amount}}"
                                   data-currency="{{$invoice->currency_code}}" data-amount="{{$amount}}" id="amount"
                                   disabled class="form-control disabled"/>
                        </div>
                    </div>

                    <div class="invoice-table" style="margin-top: 10px;">
                        <table class="table table-hover table-striped">
                            <thead>
                            <tr>
                                <th>{{ trans('fi.date') }}</th>
                                <th>{{ trans('fi.summary') }}</th>
                                <th>{{ trans('fi.total') }}</th>
                                <th>{{ trans('fi.balance') }}</th>
                                <th>{{ trans('fi.paid_amount') }}</th>
                                <th>&nbsp;</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($settlements as $settlement)
                                <tr>
                                    <td class="hidden-xs">{{ $settlement->formatted_created_at }}</td>
                                    <td class="hidden-sm hidden-xs">{{ $settlement->note }}</td>
                                    <td class="hidden-sm hidden-xs">{{ $settlement->formatted_numeric_amount }}</td>
                                    <td class="hidden-sm hidden-xs">{{ $settlement->formatted_numeric_remaining_balance }}</td>
                                    <td>
                                        <div class="input-group">
                                            <span class="input-group-addon"><i
                                                        class="fa {{getCurrencyClass($settlement->currency_code)}}"></i></span>
                                            <input type="text" id="{{'paid_amount_' . $settlement->id}}"
                                                   name="{{'paid_amount[' . $settlement->id. ']'}}"
                                                   data-currency="{{$settlement->currency_code}}"
                                                   data-amount="{{sprintf("%.2f", $settlement->remaining_balance)}}"
                                                   data-id="{{ $settlement->id }}"
                                                   disabled
                                                   value="0"
                                                   class="form-control" autocomplete="off"/>
                                        </div>
                                    </td>
                                    <td>
                                        <input type="checkbox" id="{{'prepayment_selection_' . $settlement->id}}"
                                               name="{{'prepayment_selection[' . $settlement->id .']'}}"
                                               value="{{ $settlement->id }}"
                                               data-amount="{{sprintf("%.2f", $settlement->remaining_balance)}}"
                                               title="{{($invoice->currency_code == $settlement->currency_code) ? '' : trans('fi.currency_not_match')}}"
                                               {{($invoice->currency_code == $settlement->currency_code) ? '' : 'disabled'}}
                                               data-currency="{{$settlement->currency_code}}"
                                               data-id="{{ $settlement->id }}" class="check check-aligned"/>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="col-sm-6 pull-right" style="margin-top: 10px;">
                        <div class="form-group pull-right">
                            <label class="control-label">{{ trans('fi.remaining_invoice_amount') }}:</label>

                            <div class="input-group">
                                <span class="input-group-addon"><i
                                            class="fa {{getCurrencyClass($invoice->currency_code)}}"></i></span>
                                {!! Form::text('remaining_balance', $formatted_amount , ['class' => 'form-control disabled', 'id' => 'remaining_balance','readonly'=>true, 'data-amount' => sprintf("%.2f", $amount) ]) !!}
                            </div>
                        </div>
                    </div>

                </form>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('fi.cancel') }}</button>
                @can('payments.create')
                <button type="button" id="confirm-payment-invoices" class="btn btn-primary"
                        data-text="{{ trans('fi.submit') }}"
                        data-loading-text="{{ trans('fi.please_wait') }}...">{{ trans('fi.submit') }}</button>
                @endcan
            </div>
        </div>
    </div>
</div>
