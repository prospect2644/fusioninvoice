<div class="row">
    @if(isset($invoicePaymentSummary[$currency]['totalInvoiced']) && !empty($invoicePaymentSummary[$currency]['totalInvoiced']))
        <div class="col-xs-8">
            <strong class="pull-right">{{ trans('fi.total_invoiced') }}:</strong>
        </div>
        <div class="col-xs-4">
            {{ $invoicePaymentSummary[$currency]['totalInvoiced'] ?? "-" }}
        </div>
    @endif

    @if(isset($invoicePaymentSummary[$currency]['totalPaidInvoices']) && !empty($invoicePaymentSummary[$currency]['totalPaidInvoices']))
        <div class="col-xs-8">
            <strong class="pull-right">{{ trans('fi.total_paid_invoices') }}:</strong>
        </div>
        <div class="col-xs-4">
            {{ $invoicePaymentSummary[$currency]['totalPaidInvoices'] ?? "-" }}
        </div>
    @endif
    @if(isset($invoicePaymentSummary[$currency]['totalOpenInvoices']) && !empty($invoicePaymentSummary[$currency]['totalOpenInvoices']))
        <div class="col-xs-8">
            <strong class="pull-right">{{ trans('fi.open_invoices') }}:</strong>
        </div>
        <div class="col-xs-4">
            {{ $invoicePaymentSummary[$currency]['totalOpenInvoices'] ?? "-" }}
        </div>
    @endif
    @if(isset($invoicePaymentSummary[$currency]['totalOpenCredits']) && !empty($invoicePaymentSummary[$currency]['totalOpenCredits']))
        <div class="col-xs-8">
            <strong class="pull-right">{{ trans('fi.open_credits') }}:</strong>
        </div>
        <div class="col-xs-4">
            {{ $invoicePaymentSummary[$currency]['totalOpenCredits'] ?? "-" }}
        </div>
    @endif
    @if(isset($invoicePaymentSummary[$currency]['totalUnappliedPayments']) && !empty($invoicePaymentSummary[$currency]['totalUnappliedPayments']))
        <div class="col-xs-8">
            <strong class="pull-right">{{ trans('fi.unapplied_payments') }}:</strong>
        </div>
        <div class="col-xs-4">
            {{ $invoicePaymentSummary[$currency]['totalUnappliedPayments'] ?? "-" }}
        </div>
    @endif
    @if(isset($invoicePaymentSummary[$currency]['totalBalance']) && !empty($invoicePaymentSummary[$currency]['totalBalance']))
        <div class="col-xs-8">
            <strong class="pull-right">{{ trans('fi.balance') }}:</strong>
        </div>
        <div class="col-xs-4">
            {{ $invoicePaymentSummary[$currency]['totalBalance'] ?? "-" }}
        </div>
    @endif
</div>