@if($payment->paymentInvoice->count() > 0)
    <p>Thank you! Your payment of {!! $payment->formatted_amount !!} has been applied to below Invoices</p>
    <table class="payment-table" cellpadding="0" cellspacing="0" style="min-width:100%;">
        <thead>
        <tr>
            <th style="padding: 8px 20px 5px 8px;">{{ trans('fi.invoice') }}</th>
            <th style="padding: 8px 20px 5px 8px;">{{ trans('fi.date') }}</th>
            <th style="padding: 8px 20px 5px 8px;">{{ trans('fi.due') }}</th>
            <th style="padding: 8px 20px 5px 8px;">{{ trans('fi.summary') }}</th>
            <th style="padding: 8px 20px 5px 8px;">{{ trans('fi.total') }}</th>
            <th style="padding: 8px 20px 5px 8px;">{{ trans('fi.paid') }}</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($payment->paymentInvoice as $paymentInvoice)
            <tr>
                <td style="padding: 8px 20px 5px 8px;"><a href="{{$paymentInvoice->invoice->public_url}}" style="color: #3c3cff;"> {{ $paymentInvoice->invoice->number }} </a></td>
                <td  style="padding: 8px 20px 5px 8px;" class="hidden-xs">{{ $paymentInvoice->invoice->formatted_invoice_date }}</td>
                <td  style="padding: 8px 20px 5px 8px;" class="hidden-md hidden-sm hidden-xs"
                    @if ($paymentInvoice->invoice->isOverdue) style="color: #ff0000; font-weight: bold;" @endif>{{ $paymentInvoice->invoice->formatted_due_at }}</td>
                <td  style="padding: 8px 20px 5px 8px;" class="hidden-sm hidden-xs">{{ $paymentInvoice->invoice->summary }}</td>
                <td  style="padding: 8px 20px 5px 8px;" class="hidden-sm hidden-xs">{{ $paymentInvoice->invoice->amount->formatted_total }}</td>
                <td  style="padding: 8px 20px 5px 8px;" class="hidden-sm hidden-xs">{{ $paymentInvoice->invoice->amount->formatted_paid }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
@else
    <p>Thank you for your payment of {!! $payment->formatted_amount !!}.</p>
@endif