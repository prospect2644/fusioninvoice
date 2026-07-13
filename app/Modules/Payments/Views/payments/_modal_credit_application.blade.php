@include('layouts._datepicker')
@include('payments._js_credit_applications')
<div class="modal fade modal-wide" id="modal-fetch-invoices" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">{{ trans('fi.settlement_for_credit_memo') }} # {{ $creditMemo->number }}</h4>
            </div>
            <div class="modal-body">

                <div id="modal-status-placeholder"></div>

                <form class="form-inline" id="fetch-invoices-form">
                    {!! Form::hidden('credit_memo_id', $creditMemo->id) !!}
                    {!! Form::hidden('total_paid', 0, ['id' => 'total_paid']) !!}
                    <div class="form-group" style="margin-left: 7px;">
                        <label class="control-label">{{ trans('fi.client_name') }}:</label>
                        {!! Form::text('client_name', $clientName , ['class' => 'form-control disabled', 'id' => 'client_name','disabled'=>true, 'size' => 50]) !!}
                    </div>
                    <div class="form-group" style="margin-left: 10px;">
                        <label class="control-label">{{ trans('fi.credit_memo_amount') }}:</label>

                        <div class="input-group">
                            <span class="input-group-addon"><i
                                        class="fa {{getCurrencyClass($creditMemo->currency_code)}}"></i></span>
                            <input type="text" name="amount" value="{{$formatted_amount}}"
                                   data-currency="{{$creditMemo->currency_code}}" data-amount="{{$amount}}" id="amount"
                                   disabled class="form-control disabled"/>
                        </div>
                    </div>

                    <div class="invoice-table" style="margin-top: 10px;">
                        <table class="table table-hover table-striped">
                            <thead>
                            <tr>
                                <th>{{ trans('fi.invoice') }}</th>
                                <th>{{ trans('fi.date') }}</th>
                                <th>{{ trans('fi.due') }}</th>
                                <th>{{ trans('fi.summary') }}</th>
                                <th>{{ trans('fi.total') }}</th>
                                <th>{{ trans('fi.balance') }}</th>
                                <th>{{ trans('fi.applied_amount') }}</th>
                                <th>&nbsp;</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($invoices as $invoice)
                                <tr>
                                    <td>{{ $invoice->number }}</td>
                                    <td class="hidden-xs">{{ $invoice->formatted_invoice_date }}</td>
                                    <td class="hidden-md hidden-sm hidden-xs"
                                        @if ($invoice->isOverdue) style="color: #ff0000; font-weight: bold;" @endif>{{ $invoice->formatted_due_at }}</td>
                                    <td class="hidden-sm hidden-xs">{{ $invoice->summary }}</td>
                                    <td class="hidden-sm hidden-xs">{{ $invoice->amount->formatted_total }}</td>
                                    <td class="hidden-sm hidden-xs">{{ $invoice->amount->formatted_balance }}</td>
                                    <td>
                                        <div class="input-group">
                                            <span class="input-group-addon"><i
                                                        class="fa {{getCurrencyClass($invoice->currency_code)}}"></i></span>
                                            <input type="text" id="{{'paid_amount_' . $invoice->id}}"
                                                   name="{{'paid_amount[' . $invoice->id. ']'}}"
                                                   data-currency="{{$invoice->currency_code}}"
                                                   data-amount="{{$invoice->amount->balance}}"
                                                   data-id="{{ $invoice->id }}"
                                                   disabled
                                                   value="0"
                                                   class="form-control" autocomplete="off"/>
                                        </div>
                                    </td>
                                    <td>
                                        <input type="checkbox" id="{{'invoice_selection_' . $invoice->id}}"
                                               title="{{($invoice->currency_code != $creditMemo->currency_code) ? trans('fi.currency_not_match') : ''}}"
                                               {{($invoice->currency_code == $creditMemo->currency_code) ? '' : 'disabled'}}
                                               name="{{'invoice_selection[' . $invoice->id .']'}}"
                                               value="{{ $invoice->id }}" data-amount="{{$invoice->amount->balance}}"
                                               data-currency="{{$invoice->currency_code}}"
                                               data-id="{{ $invoice->id }}" class="check check-aligned"/>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="col-sm-6 pull-right" style="margin-top: 10px;">
                        <div class="form-group pull-right">
                            <label class="control-label">{{ trans('fi.remaining_credit_balance') }}:</label>

                            <div class="input-group">
                                <span class="input-group-addon"><i
                                            class="fa {{getCurrencyClass($creditMemo->currency_code)}}"></i></span>
                                {!! Form::text('remaining_balance', $formatted_amount , ['class' => 'form-control disabled', 'id' => 'remaining_balance','readonly'=>true, 'data-amount' => sprintf("%.2f", $amount) ]) !!}
                            </div>
                            <div style="margin-top: 10px;">
                                {{ trans('fi.remaining_credit_balance_apply_later_info') }}
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
