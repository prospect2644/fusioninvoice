@extends('layouts.master')

@section('javascript')
    @include('layouts._datepicker')
    @parent
    <script type="text/javascript">
        $(function () {

            alertify.defaults.theme.ok = "ui negative button";
            alertify.defaults.theme.cancel = "ui black button";

            $("<style>").text(".ajs-header{ background-color: #ba0606 !important; }").appendTo($("body"));

            $('#btn-submit').click(function () {
                $('#form-settings').submit();
            });

            $('#btn-recalculate-invoices').click(function () {
                var $btn = $(this).button('loading');
                $.post("{{ route('invoices.recalculate') }}").done(function (response) {
                    alertify.success(response.message, 5);
                }).fail(function (response) {
                    alertify.error($.parseJSON(response.responseText).message, 5);
                }).always(function () {
                    $btn.button('reset');
                });
            });

            $('#btn-recalculate-quotes').click(function () {
                var $btn = $(this).button('loading');
                $.post("{{ route('quotes.recalculate') }}").done(function (response) {
                    alertify.success(response.message, 5);
                }).fail(function (response) {
                    alertify.error($.parseJSON(response.responseText).message, 5);
                }).always(function () {
                    $btn.button('reset');
                });
            });

            $('#setting-tabs a').click(function (e) {
                var tabId = $(e.target).attr("href").substr(1);
                $.post("{{ route('settings.saveTab') }}", {settingTabId: tabId});
            });

            $('#setting-tabs a[href="#{{ session('settingTabId') }}"]').tab('show');

            $('#btn-delete-orphan-tags').click(function () {
                let $_this = $(this);
                alertify.confirm("{!! trans('fi.orphan_tags_delete_confirm') !!}", function () {
                    var $btn = $_this.button('loading');
                    $.post("{{ route('tags.delete') }}").done(function (response) {
                        alertify.success(response.message, 5);
                    }).fail(function (response) {
                        alertify.error($.parseJSON(response.responseText).message, 5);
                    }).always(function () {
                        $btn.button('reset');
                    });
                }, function () {
                    alertify.alert().destroy();
                }).setHeader(confirmHeader).set({transition: 'zoom', defaultFocus: 'cancel'});
            });
            $('#btn-generate-timeline').click(function () {
                let $_this = $(this);
                alertify.confirm("{!! trans('fi.generating_timeline_confirm') !!}", function () {
                    var $btn = $_this.button('loading');
                    $.get("{{ route('tasks.generate_timeline_history') }}").done(function (response) {
                        if (('success' in response) && (response.success == true)) {
                            $('#btn-generate-timeline').hide();
                            alertify.success(response.message, 5);
                            setTimeout(function () {
                                window.location = "{{ route('settings.index') }}"
                            }, 3000);
                        }
                    }).fail(function (response) {
                        alertify.error($.parseJSON(response.responseText).message, 5);
                    }).always(function () {
                        $btn.button('reset');
                    });
                }, function () {
                    alertify.alert().destroy();
                }).setHeader(confirmHeader).set({transition: 'zoom', defaultFocus: 'cancel'});
            });

            $('#btn-generate-passport-key').click(function () {
                let $_this = $(this);
                var $btn = $_this.button('loading');
                $.post("{{ route('settings.generatePassportKey') }}").done(function (response) {
                    alertify.success(response.message, 5);
                }).fail(function (response) {
                    alertify.error($.parseJSON(response.responseText).message, 5);
                }).always(function () {
                    $btn.button('reset');
                });
            });

            $('#dashboard-widgets-from-date').datepicker({
                format: 'yyyy-mm-dd',
                autoclose: true
            });

            $('#dashboard-widgets-to-date').datepicker({
                format: 'yyyy-mm-dd',
                autoclose: true
            });

            $('#dashboard-widgets-date-options').click(function(){
                if($(this).val() == 'custom_date_range'){
                    $('#dashboard-widget-dates').show();
                }else{
                    $('#dashboard-widget-dates').hide();
                }
            });

            $('#btn-pdf-cleanup').click(function () {
                let $_this = $(this);
                alertify.confirm("{!! trans('fi.pdf_cleanup_confirm') !!}", function () {
                    var $btn = $_this.button('loading');
                    $.get("{{ route('settings.pdf.cleanup') }}").done(function (response) {
                        alertify.success(response.message, 5);
                    }).fail(function (response) {
                        alertify.error($.parseJSON(response.responseText).message, 5);
                    }).always(function () {
                        $btn.button('reset');
                    });
                }, function () {
                    alertify.alert().destroy();
                }).setHeader(confirmHeader).set({transition: 'zoom', defaultFocus: 'cancel'});
            });

        });
    </script>
@stop

@section('content')

    <section class="content-header">
        <h1 class="pull-left">
            {{ trans('fi.system_settings') }}
        </h1>

        <div class="pull-right">
            @if (!config('app.demo'))
                <a href="{{ route('settings.backup.database') }}" target="_blank" class="btn btn-success"><i
                            class="fa fa-database"></i> {{ trans('fi.download_database_backup') }}</a>
            @endif
            <button class="btn btn-primary" id="btn-submit"><i class="fa fa-save"></i> {{ trans('fi.save') }}</button>
        </div>
        <div class="clearfix"></div>
    </section>

    <section class="content">

        @include('layouts._alerts')

        {!! Form::open(['route' => 'settings.update', 'files' => true, 'id' => 'form-settings']) !!}

        <div class="row">
            <div class="col-md-12">

                <div class="nav-tabs-custom">
                    <ul class="nav nav-tabs" id="setting-tabs">
                        <li class="active"><a data-toggle="tab" href="#tab-general">{{ trans('fi.general') }}</a></li>
                        <li><a data-toggle="tab" href="#tab-dashboard">{{ trans('fi.dashboard') }}</a></li>
                        <li><a data-toggle="tab" href="#tab-invoices">{{ trans('fi.invoices') }}</a></li>
                        <li><a data-toggle="tab" href="#tab-quotes">{{ trans('fi.quotes') }}</a></li>
                        <li><a data-toggle="tab" href="#tab-taxes">{{ trans('fi.taxes') }}</a></li>
                        <li><a data-toggle="tab" href="#tab-email">{{ trans('fi.email') }}</a></li>
                        <li><a data-toggle="tab" href="#tab-pdf">{{ trans('fi.pdf') }}</a></li>
                        <li><a data-toggle="tab" href="#tab-online-payments">{{ trans('fi.online_payments') }}</a></li>
                        <li><a data-toggle="tab" href="#tab-system">{{ trans('fi.system') }}</a></li>
                        @if(
                            !config('fi.clientTransitionHistoryCreated')
                            || !config('fi.expenseTransitionHistoryCreated')
                            || !config('fi.invoiceTransitionHistoryCreated')
                            || !config('fi.paymentInvoiceTransitionHistoryCreated')
                            || !config('fi.paymentTransitionHistoryCreated')
                            || !config('fi.quoteTransitionHistoryCreated')
                            || !config('fi.noteTransitionHistoryCreated')
                            || !config('fi.taskTransitionHistoryCreated')
                        )
                            <li><a data-toggle="tab" href="#tab-transitions">{{ trans('fi.transitions') }}</a></li>
                        @endif
                    </ul>
                    <div class="tab-content">
                        <div id="tab-general" class="tab-pane active">
                            @include('settings._general')
                        </div>
                        <div id="tab-dashboard" class="tab-pane">
                            @include('settings._dashboard')
                        </div>
                        <div id="tab-invoices" class="tab-pane">
                            @include('settings._invoices')
                        </div>
                        <div id="tab-quotes" class="tab-pane">
                            @include('settings._quotes')
                        </div>
                        <div id="tab-taxes" class="tab-pane">
                            @include('settings._taxes')
                        </div>
                        <div id="tab-email" class="tab-pane">
                            @include('settings._email')
                        </div>
                        <div id="tab-pdf" class="tab-pane">
                            @include('settings._pdf')
                        </div>
                        <div id="tab-online-payments" class="tab-pane">
                            @include('settings._online_payments')
                        </div>
                        <div id="tab-system" class="tab-pane">
                            @include('settings._system')
                        </div>
                        <div id="tab-transitions" class="tab-pane">
                            @include('settings._transition')
                        </div>
                    </div>
                </div>

            </div>

        </div>

        {!! Form::close() !!}

    </section>

@stop