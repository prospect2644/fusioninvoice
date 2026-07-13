<script type="text/javascript">
    $(function () {
        let creditApplicationsCount = '{{$creditApplications->count()}}';
        if (creditApplicationsCount) {
            $('.credit-application-count').html(creditApplicationsCount);
        }
        else {
            $('.credit-application-count').html('');
        }

        $('.btn-delete-payment').click(function () {
            var $_this = $(this);
            alertify.defaults.theme.ok = "ui negative button";
            alertify.defaults.theme.cancel = "ui black button";
            $("<style>")
                    .text(".ajs-header{ background-color: #ba0606 !important; }")
                    .appendTo($("body"));

            alertify.confirm('{{ trans('fi.delete_record_warning') }}', function () {
                $.post('{{ route('invoices.payments.delete', [$creditMemoId]) }}', {
                    id: $_this.data('payment-invoice-id')
                }).done(function (response) {
                    if (response.success == true) {
                        location.reload();
                    } else {
                        showAlertifyErrors($.parseJSON(response.responseText).message);
                    }
                });
            }, function () {
                alertify.alert().destroy();
            }).setHeader(confirmHeader).set({transition: 'zoom', defaultFocus: 'cancel'});
        });

    });
</script>

<table class="table table-hover table-striped">

    <thead>
    <tr>
        <th width="25%">{{ trans('fi.invoice') }}</th>
        <th width="20%">{{ trans('fi.payment_date') }}</th>
        <th>{{ trans('fi.amount') }}</th>
        <th>{{ trans('fi.note') }}</th>
        @if(Gate::check('payments.update') || Gate::check('payments.delete'))
            <th width="10%">{{ trans('fi.options') }}</th>
        @endif
    </tr>
    </thead>

    <tbody>
    @foreach ($creditApplications as $paymentInvoice)
        <tr>
            <td>{{ $paymentInvoice->invoice->number }}</td>
            <td>{{ $paymentInvoice->formatted_paid_at }}</td>
            <td>{{ $paymentInvoice->formatted_invoice_amount_paid }}</td>
            <td>{{ $paymentInvoice->payment->note }}</td>
            @if(Gate::check('payments.update') || Gate::check('payments.delete'))
                <td>
                    @can('payments.delete')
                    <a class="btn btn-xs btn-danger btn-delete-payment" href="javascript:void(0);"
                       title="{{ trans('fi.delete') }}" data-payment-invoice-id="{{ $paymentInvoice->id }}">
                        <i class="fa fa-times"></i>
                    </a>
                    @endcan
                </td>
            @endif
        </tr>
    @endforeach
    </tbody>

</table>
