@extends('layouts.master')

@section('javascript')
    @include('layouts._datepicker')
    <script type="text/javascript">
        $(function () {
            $('.change-dashboard-widgets-options').click(function () {
                var option = $(this).data('id');

                $.post("{{ route('dashboard.updateWidgetSettings') }}", {
                    dashboardWidgetsDateOption: option,
                    dashboardWidgetsFromDate: $('#dashboard-widgets-from-date').val(),
                    dashboardWidgetsToDate: $('#dashboard-widgets-to-date').val()
                }, function () {
                    location.reload();
                });
            });

            $('#dashboard-widgets-from-date').datepicker({
                format: "{{ config('fi.datepickerFormat') }}",
                autoclose: true
            });
            $('#dashboard-widgets-to-date').datepicker({
                format: "{{ config('fi.datepickerFormat') }}",
                autoclose: true
            });

            $('.version-check-preference').click(function () {
                $.get("{{ route('dashboard.version.check.preference') }}", function () {
                    $('.version-info').hide();
                });
            });

            $('.dismiss-forever').click(function () {
                $.get("{{ route('dashboard.agreement.check.preference') }}", function () {
                    $('.agreement-info').hide();
                });
            });
        });
    </script>
@stop

@section('content')

    <section class="content-header clearfix">

        @include('layouts._alerts')

        @if (session()->has('versionAlert') && session('versionAlert') != null)
            <div class="box box-info version-info" style="border-bottom: 3px solid #00c0ef">
                <div class="box-header">
                    <h3 class="box-title" style="font-weight: bold">{{ trans('fi.new-version-available') }}</h3>

                    <div class="box-tools pull-right">
                        <button type="button" class="btn btn-box-tool version-check-preference" data-widget="remove"><i class="fa fa-times"></i></button>
                    </div>
                </div>
                <div class="box-body" style="font-weight: bold">
                    {{ session('versionAlert') }}
                    &nbsp;&nbsp;&nbsp;<a href="https://www.fusioninvoice.com/docs/{{date('Y')}}/About-FusionInvoice/Release-Notes" class="btn btn-primary btn-sm" target="_blank">{{ trans('fi.view-release-notes') }}</a>
                    &nbsp;&nbsp;&nbsp;<a href="#" class="btn btn-danger btn-sm version-check-preference">{{ trans('fi.ignore-this-version') }}</a>
                </div>
            </div>
        @endif

        @if (session()->has('agreementExpireAlert') && session('agreementExpireAlert') != null)
            <div class="box box-info agreement-info" style="border-bottom: 3px solid #00c0ef">
                <div class="box-header">
                    <h3 class="box-title" style="font-weight: bold">{{ trans('fi.agreement-expire-alert') }}</h3>

                    <div class="box-tools pull-right">
                        <button type="button" class="btn btn-box-tool dismiss-forever" data-widget="remove"><i class="fa fa-times"></i></button>
                    </div>
                </div>
                <div class="box-body" style="font-weight: bold">
                    {{ session('agreementExpireAlert') }}
                    &nbsp;&nbsp;&nbsp;<a href="https://www.fusioninvoice.com/store" class="btn btn-primary btn-sm" target="_blank">{{ trans('fi.renew-now') }}</a>
                    &nbsp;&nbsp;&nbsp;<a href="#" class="btn btn-danger btn-sm dismiss-forever">{{ trans('fi.dismiss-forever') }}</a>
                </div>
            </div>
        @endif

        @if (session()->has('agreementExpiredAlert') && session('agreementExpiredAlert') != null)
            <div class="box box-info agreement-info" style="border-bottom: 3px solid #00c0ef">
                <div class="box-header">
                    <h3 class="box-title" style="font-weight: bold">{{ trans('fi.agreement-expired-alert') }}</h3>

                    <div class="box-tools pull-right">
                        <button type="button" class="btn btn-box-tool dismiss-forever" data-widget="remove"><i class="fa fa-times"></i></button>
                    </div>
                </div>
                <div class="box-body" style="font-weight: bold">
                    {{ session('agreementExpiredAlert') }}
                    &nbsp;&nbsp;&nbsp;<a href="https://www.fusioninvoice.com/store" class="btn btn-primary btn-sm" target="_blank">{{ trans('fi.renew-now') }}</a>
                    &nbsp;&nbsp;&nbsp;<a href="#" class="btn btn-danger btn-sm dismiss-forever">{{ trans('fi.dismiss-forever') }}</a>
                </div>
            </div>
        @endif

        <h1 class="fa fa-dashboard pull-left"> </h1>
        <h1 class="pull-left">{{ trans('fi.dashboard') }}</h1>
        <div class="box-tools pull-right">
            @can('allow_time_period_change.view')
            <div class="btn-group">
                <button type="button" class="btn btn-box-tool dropdown-toggle" data-toggle="dropdown">
                    <i class="fa fa-calendar"></i> {{ $dashboardWidgetsDateOptions[config('fi.dashboardWidgetsDateOption')] }}
                </button>
                <ul class="dropdown-menu dropdown-menu-right" role="menu">
                    @foreach ($dashboardWidgetsDateOptions as $key => $option)
                        <li>
                            @if ($key != 'custom_date_range')
                                <a href="#" onclick="return false;" class="change-dashboard-widgets-options" data-id="{{ $key }}">{{ $option }}</a>
                            @else
                                <a href="#" onclick="return false;" data-toggle="modal" data-target="#dashboard-widgets-date-modal">{{ $option }}</a>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>
            @endcan
        </div>
    </section>
    <section class="content clearfix" style="padding-left: 0px; padding-right: 0px;">
        @foreach ($widgets as $widget)
            @if (config('fi.widgetEnabled' . $widget))
                <div class="col-md-{{ config('fi.widgetColumnWidth' . $widget) }} col-sm-12">
                    @include($widget . 'Widget')
                </div>
            @endif
        @endforeach
    </section>

    <div class="modal fade" id="dashboard-widgets-date-modal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title" id="myModalLabel">{{ trans('fi.custom_date_range') }}</h4>
                </div>
                <div class="modal-body">

                    <div class="form-group">
                        <label>{{ trans('fi.from_date') }}</label>
                        {!! Form::text('setting_dashboardWidgetsFromDate', config('fi.dashboardWidgetsFromDate') ? \Carbon\Carbon::createFromFormat('Y-m-d', config('fi.dashboardWidgetsFromDate'))->format(config('fi.dateFormat')) : '', ['class' => 'form-control', 'id' => 'dashboard-widgets-from-date']) !!}
                    </div>

                    <div class="form-group">
                        <label>{{ trans('fi.to_date') }}</label>
                        {!! Form::text('setting_dashboardWidgetsToDate', config('fi.dashboardWidgetsToDate') ? \Carbon\Carbon::createFromFormat('Y-m-d', config('fi.dashboardWidgetsToDate'))->format(config('fi.dateFormat')) : '', ['class' => 'form-control', 'id' => 'dashboard-widgets-to-date']) !!}
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('fi.cancel') }}</button>
                    <button type="button" class="btn btn-primary change-dashboard-widgets-options" data-id="custom_date_range" data-dismiss="modal">{{ trans('fi.save') }}</button>
                </div>
            </div>
        </div>
    </div>

@stop