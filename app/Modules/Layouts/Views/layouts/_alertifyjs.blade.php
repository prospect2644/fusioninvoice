<link href="{{ asset('assets/plugins/alertifyjs/css/alertify.min.css') }}" rel="stylesheet" type="text/css"/>
<link href="{{ asset('assets/plugins/alertifyjs/css/themes/default.min.css') }}" rel="stylesheet" type="text/css"/>
<script src='{{ asset('assets/plugins/alertifyjs/alertify.min.js') }}'></script>

<script type="text/javascript">
    var confirmHeader = '<span style="color:white;"> <span class="fa fa-warning fa-2x" '
            + 'style="vertical-align:middle;padding-right:10px;">'
            + '</span> ' + '{!! trans('fi.delete-confirm') !!}' + '</span>';
    var remainBalanceHeader = '<span style="color:white;"> <span class="fa fa-warning fa-2x" '
            + 'style="vertical-align:middle;padding-right:10px;">'
            + '</span> ' + '{!! trans('fi.remaining_payment_balance') !!}' + '</span>';
    var remainCreditMemoBalanceHeader = '<span style="color:white;"> <span class="fa fa-warning fa-2x" '
            + 'style="vertical-align:middle;padding-right:10px;">'
            + '</span> ' + '{!! trans('fi.remaining_credit_balance') !!}' + '</span>';
    var remainInvoiceBalanceHeader = '<span style="color:white;"> <span class="fa fa-warning fa-2x" '
            + 'style="vertical-align:middle;padding-right:10px;">'
            + '</span> ' + '{!! trans('fi.remaining_balance') !!}' + '</span>';
    var remainZeroInvoiceBalanceHeader = '<span style="color:white;">' + '{!! trans('fi.remaining_balance') !!}' + '</span>';
    var remainZeroBalanceHeader = '<span style="color:white;">' + '{!! trans('fi.remaining_payment_balance') !!}' + '</span>';
    var remainZeroCreditMemoBalanceHeader = '<span style="color:white;"> '+ '</span> ' + '{!! trans('fi.remaining_credit_balance') !!}' + '</span>';
    alertify.set('notifier', 'position', 'top-center');
</script>