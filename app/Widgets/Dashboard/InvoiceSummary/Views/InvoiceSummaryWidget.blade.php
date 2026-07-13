@can('invoice_summary.view')
<div id="invoice-dashboard-totals-widget">

    <div class="box box-solid">
        <div class="box-header">
            <h3 class="box-title">{{ trans('fi.invoice_summary') }}</h3>
            @can('invoices.create')
            <div class="box-tools pull-right">
                <button class="btn btn-box-tool create-invoice"><i class="fa fa-plus"></i> {{ trans('fi.create_invoice') }}</button>
            </div>
            @endcan
        </div>
        <div class="box-body">
            <div class="row">
                <div class="col-lg-6 col-md-12">
                    <div class="small-box bg-yellow">
                        <div class="inner">
                            <h3>{{ $invoicesTotalDraft }}</h3>

                            <p>{{ trans('fi.draft_invoices') }}</p>
                        </div>
                        <div class="icon">
                            <i class="ion ion-edit"></i>
                        </div>
                        @can('invoices.view')
                        <a href="{{ route('invoices.index') }}?status=draft" class="small-box-footer">
                            {{ trans('fi.view_draft_invoices') }} <i class="fa fa-arrow-circle-right"></i>
                        </a>
                        @endcan
                    </div>
                </div>
                <div class="col-lg-6 col-md-12">
                    <div class="small-box bg-aqua">
                        <div class="inner">
                            <h3>{{ $invoicesTotalSent }}</h3>

                            <p>{{ trans('fi.sent_invoices') }}</p>
                        </div>
                        <div class="icon">
                            <i class="ion ion-share"></i>
                        </div>
                        @can('invoices.view')
                        <a class="small-box-footer" href="{{ route('invoices.index') }}?status=sent">
                            {{ trans('fi.view_sent_invoices') }} <i class="fa fa-arrow-circle-right"></i>
                        </a>
                        @endcan
                    </div>
                </div>

                <div class="col-lg-6 col-md-12">
                    <div class="small-box bg-red">
                        <div class="inner">
                            <h3>{{ $invoicesTotalOverdue }}</h3>

                            <p>{{ trans('fi.overdue_invoices') }}</p>
                        </div>
                        <div class="icon"><i class="ion ion-alert"></i></div>
                        @can('invoices.view')
                        <a class="small-box-footer" href="{{ route('invoices.index') }}?status=overdue">
                            {{ trans('fi.view_overdue_invoices') }} <i class="fa fa-arrow-circle-right"></i>
                        </a>
                        @endcan
                    </div>
                </div>
                @can('payments.view')
                <div class="col-lg-6 col-md-12">
                    <div class="small-box bg-green">
                        <div class="inner">
                            <h3>{{ $invoicesTotalPaid }}</h3>

                            <p>{{ trans('fi.payments_collected') }}</p>
                        </div>
                        <div class="icon"><i class="ion ion-heart"></i></div>

                        <a class="small-box-footer" href="{{ route('payments.index') }}">
                            {{ trans('fi.view_payments') }} <i class="fa fa-arrow-circle-right"></i>
                        </a>

                    </div>
                </div>
                @endcan
            </div>
        </div>
    </div>

</div>
@endcan