@can('payments.update')
<script type="text/javascript">
    $(function () {
        $('.email-payment-receipt').click(function () {
            $('#modal-placeholder').load("{{ route('paymentMail.create') }}", {
                payment_id: $(this).data('payment-id'),
                redirectTo: $(this).data('redirect-to')
            }, function (response, status, req) {
                if (status == 'error') {
                    alertify.error('{{ trans('fi.problem_with_email_template') }}', 5);
                }
            });
        });
    });
</script>
<style>
    .open-balance {
        color: #00ca6d;
    }
</style>
@endcan

<table class="table table-hover table-striped">

    <thead>
    <tr>
        @if(isset($bulk_action) && $bulk_action == true)
            @can('payments.delete')
            <th>
                <div class="btn-group"><input type="checkbox" id="bulk-select-all"></div>
            </th>
            @endcan
        @endif
        <th>{!! Sortable::link('paid_at', trans('fi.payment_date'), 'payments') !!}</th>
        @if(!isset($client_view))
            <th>{!! trans('fi.client') !!}</th>
        @endif
        <th>{!! Sortable::link('payment_invoices.invoices.number', trans('fi.invoice'), 'payments') !!}</th>
        <th>{!! Sortable::link('payment_invoices.invoices.invoice_date', trans('fi.invoice_date'), 'payments') !!}</th>
        <th>{!! trans('fi.summary') !!}</th>
        <th>{!! trans('fi.amount') !!}</th>
        <th>{!! trans('fi.open_balance') !!}</th>
        <th>{!! Sortable::link('payment_methods.name', trans('fi.payment_method'), 'payments') !!}</th>
        <th>{!! Sortable::link('note', trans('fi.note'), 'payments') !!}</th>
        <th>{{ trans('fi.options') }}</th>
    </tr>
    </thead>

    <tbody>
    @foreach ($payments as $payment)


        <tr>
            @if(isset($bulk_action) && $bulk_action == true)
                @can('payments.delete')
                <td><input type="checkbox" class="bulk-record" data-id="{{ $payment->id }}"></td>
                @endcan
            @endif
            <td>{{ $payment->formatted_paid_at }}</td>
            @if(!isset($client_view))
                <td>
                    @can('clients.view')
                    @if($payment->client_id)
                        <a href="{{ route('clients.show', [$payment->client_id]) }}">{{ $payment->client->name }}</a>
                    @endif
                    @else
                        {{ $payment->client->name }}
                        @endcan
                </td>
            @endif
            <td>
                @can('invoices.update')
                @if(count($payment->paymentInvoice) == 1)
                    <a href="{{ route('invoices.edit', [$payment->paymentInvoice->first()->invoice_id]) }}">{{ $payment->paymentInvoice->first()->invoice->number }}</a>
                @elseif(count($payment->paymentInvoice) > 1)
                    <a href="javascript:void(0)"
                       data-action="{{ route('payments.applications',['payment' => $payment->id])}}"
                       class="payment-applications">
                        {{ trans('fi.multiple') }}
                    </a>
                @endif
                @endcan
            </td>
            <td>
                @if(count($payment->paymentInvoice) == 1)
                {{ $payment->paymentInvoice->first()->invoice->formatted_invoice_date }}</a>
                @elseif(count($payment->paymentInvoice) > 1)
                    {{ trans('fi.multiple') }}
                @endif
            </td>
            <td>
                @if(count($payment->paymentInvoice) == 1)
                    {{ $payment->paymentInvoice->first()->invoice->summary }}
                @endif
            </td>
            <td>{{ $payment->formatted_amount_with_currency }}</td>
            <td>
                <span class="{{($payment->remaining_balance) ? 'open-balance' : ''}}">{{ $payment->formatted_remaining_balance_with_currency }}</span>
            </td>
            <td>@if ($payment->paymentMethod) {{ $payment->paymentMethod->name }} @endif</td>
            <td>{{ $payment->note }}</td>
            <td>
                <div class="btn-group">
                    <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown">
                        {{ trans('fi.options') }} <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-right">
                        @if($payment->amount == $payment->remaining_balance)
                            @can('payments.update')
                            <li>
                                <a href="javascript:void(0)" class="edit-payment"
                                   data-action="{{ route('payments.editPayment',['payment' => $payment->id]) }}">
                                    <i class="fa fa-edit"></i> {{ trans('fi.edit') }}
                                </a>
                            </li>
                            @endcan
                        @endif
                        @if(count($payment->paymentInvoice) == 1)
                            @can('invoices.view')
                            <li>
                                <a href="{{ route('invoices.pdf', [$payment->paymentInvoice->first()->invoice_id]) }}"
                                   target="_blank" id="btn-pdf-invoice"><i
                                            class="fa fa-print"></i> {{ trans('fi.invoice_pdf') }}</a></li>
                            @endcan
                        @endif
                        @if(count($payment->paymentInvoice) > 0)
                            @can('invoices.view')
                            <li>
                                <a href="javascript:void(0)"
                                   data-action="{{ route('payments.applications',['payment' => $payment->id])}}"
                                   class="payment-applications">
                                    <i class="fa fa-usd"></i> {{ trans('fi.payment_applications') }}
                                </a>
                            </li>
                            @endcan
                        @endif
                        @if (config('fi.mailConfigured'))
                            @can('payments.update')
                            <li><a href="javascript:void(0)" class="email-payment-receipt"
                                   data-payment-id="{{ $payment->id }}"
                                   data-redirect-to="{{ request()->fullUrl() }}"><i
                                            class="fa fa-envelope"></i> {{ trans('fi.email_payment_receipt') }}</a>
                            </li>
                            @endcan
                        @endif
                        @can('payments.delete')
                        <li><a href="#" data-action="{{ route('payments.delete', [$payment->id]) }}"
                               class="delete-payment text-danger"><i
                                        class="fa fa-trash-o"></i> {{ trans('fi.delete') }}</a></li>
                        @endcan
                    </ul>
                </div>
            </td>
        </tr>

    @endforeach
    </tbody>

</table>