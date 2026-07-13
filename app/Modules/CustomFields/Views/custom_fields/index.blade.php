@extends('layouts.master')

@section('content')
    @include('layouts._formdata')
    <script type="text/javascript">
        $(function () {
            var fixHelper = function (e, tr) {
                var $originals = tr.children();
                var $helper = tr.clone();
                $helper.children().each(function (index) {
                    $(this).width($originals.eq(index).width())
                });
                return $helper;
            };

            $(".custom-fields tbody").sortable({
                helper: fixHelper,
                update: function () {
                    var Lists = $(this).find('.order-id');
                    var reOrder = [];
                    var type = '';

                    $.each(Lists, function (key, value) {
                        reOrder.push($(value).val());
                        type = $(value).data('type')
                    });

                    var form_data = objectToFormData({ids: reOrder, type: type});
                    $.ajax({
                        url: '{{ route('customFields.reorder') }}',
                        method: 'post',
                        data: form_data,
                        processData: false,
                        contentType: false
                    }).done(function () {
                        alertify.success('{{ trans('fi.record_successfully_updated') }}', 5);
                    }).fail(function (response) {
                        $.each($.parseJSON(response.responseText).errors, function (id, message) {
                            alertify.error(message[0], 5);
                        });
                    });

                }
            });

            alertify.defaults.theme.ok = "ui negative button";
            alertify.defaults.theme.cancel = "ui black button";

            $("<style>").text(".ajs-header{ background-color: #ba0606 !important; }").appendTo($("body"));

            $('.delete-custom-field').click(function () {

                var $_this = $(this);

                alertify.confirm("{!! trans('fi.delete_record_warning') !!}", function () {
                    window.location = decodeURIComponent($_this.data('action'));
                }, function () {
                    alertify.alert().destroy();
                }).setHeader(confirmHeader).set({transition: 'zoom', defaultFocus: 'cancel'});

            });

            $('#btn-bulk-delete').click(function () {

                var ids = [];
                $('.custom-field-bulk-record:checked').each(function () {
                    ids.push($(this).data('id'));
                });

                if (ids.length > 0) {

                    alertify.confirm("{!! trans('fi.delete_record_warning') !!}", function () {
                        $.ajax({
                            url: "{{ route('customFields.bulk.delete') }}",
                            method: 'post',
                            data: {ids: ids},
                            beforeSend: function () {
                                $(".modal-loader").show();
                            },
                            success: function () {
                                $(".modal-loader").hide();
                                window.location = decodeURIComponent("{{ urlencode(request()->fullUrl()) }}");
                            }
                        });
                    }, function () {
                        alertify.alert().destroy();
                    }).setHeader(confirmHeader).set({transition: 'zoom', defaultFocus: 'cancel'});

                }
            });


            $('.custom-field-bulk-select-all').click(function () {
                if ($(this).prop('checked')) {
                    $(this).closest('table').find('.custom-field-bulk-record').prop('checked', true);
                    if ($(this).closest('table').find('.custom-field-bulk-record:checked').length > 0) {
                        $('.bulk-actions').show();
                    }
                }
                else {
                    $(this).closest('table').find('.custom-field-bulk-record').prop('checked', false);
                    $('.bulk-actions').hide();
                }
            });

            $('.custom-field-bulk-record').click(function () {
                if (!$(this).prop('checked')) {
                    $(this).closest('table').find('.custom-field-bulk-select-all').prop('checked', false);
                }else{
                    var isAllChecked = 1;

                    $(this).closest('table').find('.custom-field-bulk-record').each(function() {
                        if (!this.checked)
                            isAllChecked = 0;
                    });

                    if (isAllChecked == 1) {
                        $(this).closest('table').find('.custom-field-bulk-select-all').prop('checked', true);
                    }
                }

                if ($(this).closest('table').find('.custom-field-bulk-record:checked').length > 0) {
                    $('.bulk-actions').show();
                }
                else {
                    $('.bulk-actions').hide();
                    $(this).closest('table').find('.custom-field-bulk-select-all').prop('checked', false);
                }
            });

            let customFieldsCreateUrl = '{{ route('customFields.create') }}';
            $('.nav-tabs>li').click(function(){
                let tableName = $(this).find('.nav-tab-link').data('tableName');
                let customFieldCreateUrlWithTable = customFieldsCreateUrl + '?table=' + tableName;
                $('#btn-create-custom-field').attr('href', customFieldCreateUrlWithTable);
            });

            let selectedTab = '{!! '#nav-tab-' . $selectedTab !!}';
            $(selectedTab).trigger('click');
        });
    </script>
    <section class="content-header">
        <h1 class="pull-left">
            {{ trans('fi.custom_fields') }}
        </h1>

        <div class="pull-right">
            <a href="javascript:void(0)" class="btn btn-danger bulk-actions" id="btn-bulk-delete"><i class="fa fa-trash"></i> {{ trans('fi.delete') }}</a>
            <a href="{{ route('customFields.create') }}" class="btn btn-primary" id="btn-create-custom-field"><i class="fa fa-plus"></i> {{ trans('fi.new') }}</a>
        </div>
        <div class="clearfix"></div>
    </section>

    <section class="content">

        @include('layouts._alerts')

        <div class="row">

            <div class="col-xs-12">
                <div class="nav-tabs-custom">
                    <ul class="nav nav-tabs">
                        <li class="active"><a id="nav-tab-clients" class="nav-tab-link" data-toggle="tab" href="#tab-clients" data-table-name="clients">{{ trans('fi.clients') }}</a></li>
                        <li><a id="nav-tab-company_profiles" class="nav-tab-link" data-toggle="tab" href="#tab-company-profiles" data-table-name="company_profiles">{{ trans('fi.company_profiles') }}</a></li>
                        <li><a id="nav-tab-expenses" class="nav-tab-link" data-toggle="tab" href="#tab-expenses" data-table-name="expenses">{{ trans('fi.expenses') }}</a></li>
                        <li><a id="nav-tab-invoices" class="nav-tab-link" data-toggle="tab" href="#tab-invoices" data-table-name="invoices">{{ trans('fi.invoices') }}</a></li>
                        <li><a id="nav-tab-quotes" class="nav-tab-link" data-toggle="tab" href="#tab-quotes" data-table-name="quotes">{{ trans('fi.quotes') }}</a></li>
                        <li><a id="nav-tab-recurring_invoices" class="nav-tab-link" data-toggle="tab" href="#tab-recurring-invoices" data-table-name="recurring_invoices">{{ trans('fi.recurring_invoices') }}</a></li>
                        <li><a id="nav-tab-payments" class="nav-tab-link" data-toggle="tab" href="#tab-payments" data-table-name="payments">{{ trans('fi.payments') }}</a></li>
                        <li><a id="nav-tab-users" class="nav-tab-link" data-toggle="tab" href="#tab-users" data-table-name="users">{{ trans('fi.users') }}</a></li>
                    </ul>
                    <div class="tab-content">

                        <div id="tab-clients" class="tab-pane active">

                            <div class="row">

                                <div class="col-md-12">
                                    <div class="box box-primary">

                                        <div class="box-body no-padding">
                                            <table class="table table-hover table-striped custom-fields">

                                                <thead>
                                                <tr>
                                                    <th><div class="btn-group"><input type="checkbox" class="custom-field-bulk-select-all"></div></th>
                                                    <th class="display_order">{!! Sortable::link('display_order', trans('fi.display_order')) !!}</th>
                                                    <th>{!! trans('fi.table_name') !!}</th>
                                                    <th>{!! Sortable::link('column_name', trans('fi.column_name')) !!}</th>
                                                    <th>{!! Sortable::link('field_label', trans('fi.field_label')) !!}</th>
                                                    <th>{!! Sortable::link('field_type', trans('fi.field_type')) !!}</th>
                                                    <th>{{ trans('fi.options') }}</th>
                                                </tr>
                                                </thead>

                                                <tbody>
                                                @foreach ($customFields as $customField)
                                                    @if($customField->tbl_name == 'clients')
                                                        <tr>
                                                            <td><input type="checkbox" class="custom-field-bulk-record" data-id="{{ $customField->id }}"></td>
                                                            <td>
                                                                <i class="fa fa-sort"></i>
                                                                <input type="hidden" value="{{ $customField->id }}" data-type="clients" class="order-id">
                                                            </td>
                                                            <td>{{ $tableNames[$customField->tbl_name] }}</td>
                                                            <td>{{ $customField->column_name }}</td>
                                                            <td>{{ $customField->field_label }}</td>
                                                            <td>{{ $customField->field_type }}</td>
                                                            <td>
                                                                <div class="btn-group">
                                                                    <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown">
                                                                        {{ trans('fi.options') }} <span class="caret"></span>
                                                                    </button>
                                                                    <ul class="dropdown-menu dropdown-menu-right">
                                                                        <li><a href="{{ route('customFields.edit', [$customField->id]) }}"><i class="fa fa-edit"></i> {{ trans('fi.edit') }}</a></li>
                                                                        <li><a href="#"
                                                                               data-action="{{ route('customFields.delete', [$customField->id])}}"
                                                                               class="delete-custom-field text-danger"><i
                                                                                        class="fa fa-trash-o"></i> {{trans('fi.delete') }}
                                                                            </a></li>
                                                                    </ul>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    @endif
                                                @endforeach
                                                </tbody>

                                            </table>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="tab-company-profiles" class="tab-pane">

                            <div class="row">

                                <div class="col-md-12">
                                    <div class="box box-primary">

                                        <div class="box-body no-padding">
                                            <table class="table table-hover table-striped custom-fields">

                                                <thead>
                                                <tr>
                                                    <th><div class="btn-group"><input type="checkbox" class="custom-field-bulk-select-all"></div></th>
                                                    <th class="display_order">{!! Sortable::link('display_order', trans('fi.display_order')) !!}</th>
                                                    <th>{!! trans('fi.table_name') !!}</th>
                                                    <th>{!! Sortable::link('column_name', trans('fi.column_name')) !!}</th>
                                                    <th>{!! Sortable::link('field_label', trans('fi.field_label')) !!}</th>
                                                    <th>{!! Sortable::link('field_type', trans('fi.field_type')) !!}</th>
                                                    <th>{{ trans('fi.options') }}</th>
                                                </tr>
                                                </thead>

                                                <tbody>
                                                @foreach ($customFields as $customField)
                                                    @if($customField->tbl_name == 'company_profiles')
                                                        <tr>
                                                            <td><input type="checkbox" class="custom-field-bulk-record" data-id="{{ $customField->id }}"></td>
                                                            <td>
                                                                <i class="fa fa-sort"></i>
                                                                <input type="hidden" value="{{ $customField->id }}" data-type="company_profiles" class="order-id">
                                                            </td>
                                                            <td>{{ $tableNames[$customField->tbl_name] }}</td>
                                                            <td>{{ $customField->column_name }}</td>
                                                            <td>{{ $customField->field_label }}</td>
                                                            <td>{{ $customField->field_type }}</td>
                                                            <td>
                                                                <div class="btn-group">
                                                                    <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown">
                                                                        {{ trans('fi.options') }} <span class="caret"></span>
                                                                    </button>
                                                                    <ul class="dropdown-menu dropdown-menu-right">
                                                                        <li><a href="{{ route('customFields.edit', [$customField->id]) }}"><i class="fa fa-edit"></i> {{ trans('fi.edit') }}</a></li>
                                                                        <li><a href="#"
                                                                               data-action="{{ route('customFields.delete', [$customField->id])}}"
                                                                               class="delete-custom-field text-danger"><i
                                                                                        class="fa fa-trash-o"></i> {{trans('fi.delete') }}
                                                                            </a></li>
                                                                    </ul>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    @endif
                                                @endforeach
                                                </tbody>

                                            </table>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="tab-expenses" class="tab-pane">

                            <div class="row">

                                <div class="col-md-12">
                                    <div class="box box-primary">

                                        <div class="box-body no-padding">
                                            <table class="table table-hover table-striped custom-fields">

                                                <thead>
                                                <tr>
                                                    <th><div class="btn-group"><input type="checkbox" class="custom-field-bulk-select-all"></div></th>
                                                    <th  class="display_order">{!! Sortable::link('display_order', trans('fi.display_order')) !!}</th>
                                                    <th>{!! trans('fi.table_name') !!}</th>
                                                    <th>{!! Sortable::link('column_name', trans('fi.column_name')) !!}</th>
                                                    <th>{!! Sortable::link('field_label', trans('fi.field_label')) !!}</th>
                                                    <th>{!! Sortable::link('field_type', trans('fi.field_type')) !!}</th>
                                                    <th>{{ trans('fi.options') }}</th>
                                                </tr>
                                                </thead>

                                                <tbody>
                                                @foreach ($customFields as $customField)
                                                    @if($customField->tbl_name == 'expenses')
                                                        <tr>
                                                            <td><input type="checkbox" class="custom-field-bulk-record" data-id="{{ $customField->id }}"></td>
                                                            <td>
                                                                <i class="fa fa-sort"></i>
                                                                <input type="hidden" value="{{ $customField->id }}" data-type="expenses" class="order-id">
                                                            </td>
                                                            <td>{{ $tableNames[$customField->tbl_name] }}</td>
                                                            <td>{{ $customField->column_name }}</td>
                                                            <td>{{ $customField->field_label }}</td>
                                                            <td>{{ $customField->field_type }}</td>
                                                            <td>
                                                                <div class="btn-group">
                                                                    <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown">
                                                                        {{ trans('fi.options') }} <span class="caret"></span>
                                                                    </button>
                                                                    <ul class="dropdown-menu dropdown-menu-right">
                                                                        <li><a href="{{ route('customFields.edit', [$customField->id]) }}"><i class="fa fa-edit"></i> {{ trans('fi.edit') }}</a></li>
                                                                        <li><a href="#"
                                                                               data-action="{{ route('customFields.delete', [$customField->id])}}"
                                                                               class="delete-custom-field text-danger"><i
                                                                                        class="fa fa-trash-o"></i> {{trans('fi.delete') }}
                                                                            </a></li>
                                                                    </ul>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    @endif
                                                @endforeach
                                                </tbody>

                                            </table>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="tab-invoices" class="tab-pane">

                            <div class="row">

                                <div class="col-md-12">
                                    <div class="box box-primary">

                                        <div class="box-body no-padding">
                                            <table class="table table-hover table-striped custom-fields">

                                                <thead>
                                                <tr>
                                                    <th><div class="btn-group"><input type="checkbox" class="custom-field-bulk-select-all"></div></th>
                                                    <th  class="display_order">{!! Sortable::link('display_order', trans('fi.display_order')) !!}</th>
                                                    <th>{!! trans('fi.table_name') !!}</th>
                                                    <th>{!! Sortable::link('column_name', trans('fi.column_name')) !!}</th>
                                                    <th>{!! Sortable::link('field_label', trans('fi.field_label')) !!}</th>
                                                    <th>{!! Sortable::link('field_type', trans('fi.field_type')) !!}</th>
                                                    <th>{{ trans('fi.options') }}</th>
                                                </tr>
                                                </thead>

                                                <tbody>
                                                @foreach ($customFields as $customField)
                                                    @if($customField->tbl_name == 'invoices')
                                                        <tr>
                                                            <td><input type="checkbox" class="custom-field-bulk-record" data-id="{{ $customField->id }}"></td>
                                                            <td>
                                                                <i class="fa fa-sort"></i>
                                                                <input type="hidden" value="{{ $customField->id }}" data-type="invoices" class="order-id">
                                                            </td>
                                                            <td>{{ $tableNames[$customField->tbl_name] }}</td>
                                                            <td>{{ $customField->column_name }}</td>
                                                            <td>{{ $customField->field_label }}</td>
                                                            <td>{{ $customField->field_type }}</td>
                                                            <td>
                                                                <div class="btn-group">
                                                                    <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown">
                                                                        {{ trans('fi.options') }} <span class="caret"></span>
                                                                    </button>
                                                                    <ul class="dropdown-menu dropdown-menu-right">
                                                                        <li><a href="{{ route('customFields.edit', [$customField->id]) }}"><i class="fa fa-edit"></i> {{ trans('fi.edit') }}</a></li>
                                                                        <li><a href="#"
                                                                               data-action="{{ route('customFields.delete', [$customField->id])}}"
                                                                               class="delete-custom-field text-danger"><i
                                                                                        class="fa fa-trash-o"></i> {{trans('fi.delete') }}
                                                                            </a></li>
                                                                    </ul>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    @endif
                                                @endforeach
                                                </tbody>

                                            </table>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="tab-quotes" class="tab-pane">

                            <div class="row">

                                <div class="col-md-12">
                                    <div class="box box-primary">

                                        <div class="box-body no-padding">
                                            <table class="table table-hover table-striped custom-fields">

                                                <thead>
                                                <tr>
                                                    <th><div class="btn-group"><input type="checkbox" class="custom-field-bulk-select-all"></div></th>
                                                    <th  class="display_order">{!! Sortable::link('display_order', trans('fi.display_order')) !!}</th>
                                                    <th>{!! trans('fi.table_name') !!}</th>
                                                    <th>{!! Sortable::link('column_name', trans('fi.column_name')) !!}</th>
                                                    <th>{!! Sortable::link('field_label', trans('fi.field_label')) !!}</th>
                                                    <th>{!! Sortable::link('field_type', trans('fi.field_type')) !!}</th>
                                                    <th>{{ trans('fi.options') }}</th>
                                                </tr>
                                                </thead>

                                                <tbody>
                                                @foreach ($customFields as $customField)
                                                    @if($customField->tbl_name == 'quotes')
                                                        <tr>
                                                            <td><input type="checkbox" class="custom-field-bulk-record" data-id="{{ $customField->id }}"></td>
                                                            <td>
                                                                <i class="fa fa-sort"></i>
                                                                <input type="hidden" value="{{ $customField->id }}" data-type="quotes" class="order-id">
                                                            </td>
                                                            <td>{{ $tableNames[$customField->tbl_name] }}</td>
                                                            <td>{{ $customField->column_name }}</td>
                                                            <td>{{ $customField->field_label }}</td>
                                                            <td>{{ $customField->field_type }}</td>
                                                            <td>
                                                                <div class="btn-group">
                                                                    <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown">
                                                                        {{ trans('fi.options') }} <span class="caret"></span>
                                                                    </button>
                                                                    <ul class="dropdown-menu dropdown-menu-right">
                                                                        <li><a href="{{ route('customFields.edit', [$customField->id]) }}"><i class="fa fa-edit"></i> {{ trans('fi.edit') }}</a></li>
                                                                        <li><a href="#"
                                                                               data-action="{{ route('customFields.delete', [$customField->id])}}"
                                                                               class="delete-custom-field text-danger"><i
                                                                                        class="fa fa-trash-o"></i> {{trans('fi.delete') }}
                                                                            </a></li>
                                                                    </ul>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    @endif
                                                @endforeach
                                                </tbody>

                                            </table>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="tab-recurring-invoices" class="tab-pane">

                            <div class="row">

                                <div class="col-md-12">
                                    <div class="box box-primary">

                                        <div class="box-body no-padding">
                                            <table class="table table-hover table-striped custom-fields">

                                                <thead>
                                                <tr>
                                                    <th><div class="btn-group"><input type="checkbox" class="custom-field-bulk-select-all"></div></th>
                                                    <th  class="display_order">{!! Sortable::link('display_order', trans('fi.display_order')) !!}</th>
                                                    <th>{!! trans('fi.table_name') !!}</th>
                                                    <th>{!! Sortable::link('column_name', trans('fi.column_name')) !!}</th>
                                                    <th>{!! Sortable::link('field_label', trans('fi.field_label')) !!}</th>
                                                    <th>{!! Sortable::link('field_type', trans('fi.field_type')) !!}</th>
                                                    <th>{{ trans('fi.options') }}</th>
                                                </tr>
                                                </thead>

                                                <tbody>
                                                @foreach ($customFields as $customField)
                                                    @if($customField->tbl_name == 'recurring_invoices')
                                                        <tr>
                                                            <td><input type="checkbox" class="custom-field-bulk-record" data-id="{{ $customField->id }}"></td>
                                                            <td>
                                                                <i class="fa fa-sort"></i>
                                                                <input type="hidden" value="{{ $customField->id }}" data-type="recurring_invoices" class="order-id">
                                                            </td>
                                                            <td>{{ $tableNames[$customField->tbl_name] }}</td>
                                                            <td>{{ $customField->column_name }}</td>
                                                            <td>{{ $customField->field_label }}</td>
                                                            <td>{{ $customField->field_type }}</td>
                                                            <td>
                                                                <div class="btn-group">
                                                                    <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown">
                                                                        {{ trans('fi.options') }} <span class="caret"></span>
                                                                    </button>
                                                                    <ul class="dropdown-menu dropdown-menu-right">
                                                                        <li><a href="{{ route('customFields.edit', [$customField->id]) }}"><i class="fa fa-edit"></i> {{ trans('fi.edit') }}</a></li>
                                                                        <li><a href="#"
                                                                               data-action="{{ route('customFields.delete', [$customField->id])}}"
                                                                               class="delete-custom-field text-danger"><i
                                                                                        class="fa fa-trash-o"></i> {{trans('fi.delete') }}
                                                                            </a></li>
                                                                    </ul>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    @endif
                                                @endforeach
                                                </tbody>

                                            </table>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="tab-payments" class="tab-pane">

                            <div class="row">

                                <div class="col-md-12">
                                    <div class="box box-primary">

                                        <div class="box-body no-padding">
                                            <table class="table table-hover table-striped custom-fields">

                                                <thead>
                                                <tr>
                                                    <th><div class="btn-group"><input type="checkbox" class="custom-field-bulk-select-all"></div></th>
                                                    <th  class="display_order">{!! Sortable::link('display_order', trans('fi.display_order')) !!}</th>
                                                    <th>{!! trans('fi.table_name') !!}</th>
                                                    <th>{!! Sortable::link('column_name', trans('fi.column_name')) !!}</th>
                                                    <th>{!! Sortable::link('field_label', trans('fi.field_label')) !!}</th>
                                                    <th>{!! Sortable::link('field_type', trans('fi.field_type')) !!}</th>
                                                    <th>{{ trans('fi.options') }}</th>
                                                </tr>
                                                </thead>

                                                <tbody>
                                                @foreach ($customFields as $customField)
                                                    @if($customField->tbl_name == 'payments')
                                                        <tr>
                                                            <td><input type="checkbox" class="custom-field-bulk-record" data-id="{{ $customField->id }}"></td>
                                                            <td>
                                                                <i class="fa fa-sort"></i>
                                                                <input type="hidden" value="{{ $customField->id }}" data-type="payments" class="order-id">
                                                            </td>
                                                            <td>{{ $tableNames[$customField->tbl_name] }}</td>
                                                            <td>{{ $customField->column_name }}</td>
                                                            <td>{{ $customField->field_label }}</td>
                                                            <td>{{ $customField->field_type }}</td>
                                                            <td>
                                                                <div class="btn-group">
                                                                    <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown">
                                                                        {{ trans('fi.options') }} <span class="caret"></span>
                                                                    </button>
                                                                    <ul class="dropdown-menu dropdown-menu-right">
                                                                        <li><a href="{{ route('customFields.edit', [$customField->id]) }}"><i class="fa fa-edit"></i> {{ trans('fi.edit') }}</a></li>
                                                                        <li><a href="#"
                                                                               data-action="{{ route('customFields.delete', [$customField->id])}}"
                                                                               class="delete-custom-field text-danger"><i
                                                                                        class="fa fa-trash-o"></i> {{trans('fi.delete') }}
                                                                            </a></li>
                                                                    </ul>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    @endif
                                                @endforeach
                                                </tbody>

                                            </table>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="tab-users" class="tab-pane">

                            <div class="row">

                                <div class="col-md-12">
                                    <div class="box box-primary">

                                        <div class="box-body no-padding">
                                            <table class="table table-hover table-striped custom-fields">

                                                <thead>
                                                <tr>
                                                    <th><div class="btn-group"><input type="checkbox" class="custom-field-bulk-select-all"></div></th>
                                                    <th  class="display_order">{!! Sortable::link('display_order', trans('fi.display_order')) !!}</th>
                                                    <th>{!! trans('fi.table_name') !!}</th>
                                                    <th>{!! Sortable::link('column_name', trans('fi.column_name')) !!}</th>
                                                    <th>{!! Sortable::link('field_label', trans('fi.field_label')) !!}</th>
                                                    <th>{!! Sortable::link('field_type', trans('fi.field_type')) !!}</th>
                                                    <th>{{ trans('fi.options') }}</th>
                                                </tr>
                                                </thead>

                                                <tbody>
                                                @foreach ($customFields as $customField)
                                                    @if($customField->tbl_name == 'users')
                                                        <tr>
                                                            <td><input type="checkbox" class="custom-field-bulk-record" data-id="{{ $customField->id }}"></td>
                                                            <td>
                                                                <i class="fa fa-sort"></i>
                                                                <input type="hidden" value="{{ $customField->id }}" data-type="users" class="order-id">
                                                            </td>
                                                            <td>{{ $tableNames[$customField->tbl_name] }}</td>
                                                            <td>{{ $customField->column_name }}</td>
                                                            <td>{{ $customField->field_label }}</td>
                                                            <td>{{ $customField->field_type }}</td>
                                                            <td>
                                                                <div class="btn-group">
                                                                    <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown">
                                                                        {{ trans('fi.options') }} <span class="caret"></span>
                                                                    </button>
                                                                    <ul class="dropdown-menu dropdown-menu-right">
                                                                        <li><a href="{{ route('customFields.edit', [$customField->id]) }}"><i class="fa fa-edit"></i> {{ trans('fi.edit') }}</a></li>
                                                                        <li><a href="#"
                                                                               data-action="{{ route('customFields.delete', [$customField->id])}}"
                                                                               class="delete-custom-field text-danger"><i
                                                                                        class="fa fa-trash-o"></i> {{trans('fi.delete') }}
                                                                            </a></li>
                                                                    </ul>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    @endif
                                                @endforeach
                                                </tbody>

                                            </table>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

            </div>

        </div>

    </section>

@stop