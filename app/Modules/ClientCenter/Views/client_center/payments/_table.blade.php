<table class="table table-hover table-striped">
    <thead>
    <tr>
        <th>{{ trans('fi.date') }}</th>
        <th>{{ trans('fi.invoice') }}</th>
        <th>{{ trans('fi.summary') }}</th>
        <th>{{ trans('fi.amount') }}</th>
        <th>{{ trans('fi.payment_method') }}</th>
    </tr>
    </thead>
    <tbody>
    @foreach ($payments as $payment)
        <tr>
            <td>{{ $payment->formatted_paid_at }}</td>
            <td>
                @if(count($payment->paymentInvoice) == 1)
                    <a href="{{ route('clientCenter.public.invoice.show', [$payment->paymentInvoice->first()->invoice->url_key]) }}">{{ $payment->paymentInvoice->first()->invoice->number }}</a>
                @elseif(count($payment->paymentInvoice) > 1)
                    <a href="javascript:void(0)" data-action="{{ route('payments.applications',['payment' => $payment->id])}}" class="payment-applications">
                        {{ trans('fi.multiple') }}
                    </a>
                @endif
            </td>
            <td>{{ $payment->paymentInvoice->count() > 0 ? $payment->paymentInvoice->first()->invoice->summary : '' }}</td>
            <td>{{ $payment->paymentInvoice->count() > 0 ? $payment->paymentInvoice->first()->formatted_invoice_amount_paid : $payment->formatted_amount }}</td>
            <td>{{ $payment->paymentMethod->name }}</td>
        </tr>
    @endforeach
    </tbody>
</table>