<script type="text/javascript">
    $(function () {
        $('#modal-invoice-list').modal();
    });
</script>
<div class="modal fade modal-wide" id="modal-invoice-list" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">{{ trans('fi.payment_application') }}</h4>
            </div>
            <div class="modal-body">

                <div id="modal-status-placeholder"></div>

                <form class="form-inline" id="fetch-invoices-form">

                    <div class="form-group" style="margin-left: 7px;">
                        <label class="control-label">{{ trans('fi.client_name') }}:</label>
                        {!! Form::text('client_name', $clientName , ['class' => 'form-control disabled', 'id' => 'client_name','disabled'=>true, 'size' => 50]) !!}
                    </div>
                    <div class="form-group" style="margin-left: 10px;">
                        <label class="control-label">{{ trans('fi.payment_amount') }}:</label>
                        <input type="text" name="amount" value="{{$payment->formatted_numeric_amount}}" id="amount" disabled class="form-control disabled"/>
                    </div>

                    <table class="table table-hover table-striped">
                        <thead>
                        <tr>
                            <th>{{ trans('fi.invoice') }}</th>
                            <th>{{ trans('fi.date') }}</th>
                            <th>{{ trans('fi.due') }}</th>
                            <th>{{ trans('fi.summary') }}</th>
                            <th>{{ trans('fi.total') }}</th>
                            <th>{{ trans('fi.paid') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($paymentInvoices as $paymentInvoice)
                            <tr>
                                <td>{{ $paymentInvoice->number }}</td>
                                <td class="hidden-xs">{{ $paymentInvoice->formatted_invoice_date }}</td>
                                <td class="hidden-md hidden-sm hidden-xs" @if ($paymentInvoice->isOverdue) style="color: #ff0000; font-weight: bold;" @endif>{{ $paymentInvoice->formatted_due_at }}</td>
                                <td class="hidden-sm hidden-xs">{{ $paymentInvoice->summary }}</td>
                                <td class="hidden-sm hidden-xs">{{ $paymentInvoice->amount->formatted_total }}</td>
                                <td class="hidden-sm hidden-xs">{{ $paymentInvoice->invoice_amount_paid }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    <div class="col-sm-6 pull-right">
                        <div class="form-group pull-right">
                            <label class="control-label">{{ trans('fi.remaining_payment_balance') }}:</label>
                            {!! Form::text('remaining_balance', $payment->formatted_numeric_remaining_balance , ['class' => 'form-control disabled', 'id' => 'remaining_balance','readonly'=>true]) !!}
                            <div style="margin-top: 10px;">
                                {{ trans('fi.remaining_payment_balance_apply_later_info') }}
                            </div>
                        </div>
                    </div>

                </form>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-dismiss="modal">{{ trans('fi.ok') }}</button>
            </div>
        </div>
    </div>
</div>