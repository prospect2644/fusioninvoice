@extends('layouts.master')

@section('javascript')

    @include('layouts._datepicker')
    @include('layouts._colorpicker')
    @include('layouts._select2')

@stop

@section('content')

    <script type="text/javascript">
        $(function () {
            $('#name').focus();

            $('#btn-delete-custom-img').click(function () {
                var url = "{{ route('users.deleteImage', [isset($user->id) ? $user->id : '','field_name' => '']) }}";
                $.post(url + '/' + $(this).data('field-name')).done(function () {
                    $('.custom_img').html('');
                });
            });

            $(document).ready(function () {
                $('.permission-is_view').not(':checked').each(function () {
                    $(this).closest('tr')
                        .find('.permission-checkbox').not('.permission-is_view')
                        .prop('checked', false)
                        .prop('disabled', true);
                });
            });

            $('.addons,.reports,.dashboards').change(function () {
                if (!this.checked) {
                    $("#all-permissions").prop('checked', false);
                }
            });

            $('.permission-is_view,.permission-is_create,.permission-is_update,.permission-is_delete').change(function () {
                if (!this.checked) {
                    $(this).closest('tr').find('.check-all').prop('checked', false);
                    $("#all-permissions").prop('checked', false);
                } else {
                    var chk_count = 0;
                    $(this).closest('tr')
                            .find('.permission-checkbox').not(':checked').each(function () {
                                chk_count++;
                            });
                    if(chk_count == 0){
                        $(this).closest('tr').find('.check-all').prop('checked', true);
                    }
                }
            });
            
            $('.permission-is_view').change(function() {
                if(this.checked)
                {
                    $(this).closest('tr')
                        .find('.permission-checkbox').not('.permission-is_view')
                        .prop('checked', false)
                        .removeAttr('disabled');
                } else {
                    $(this).closest('tr')
                        .find('.permission-checkbox').not('.permission-is_view')
                        .prop('checked', false)
                        .prop('disabled', true);
                    $("#all-permissions").prop('checked', false);
                }
            });

            $('.check-all').change(function(){
                if(this.checked)
                {
                    $(this).closest('tr')
                        .find('.permission-checkbox')
                        .prop('checked', true)
                        .removeAttr('disabled');
                } else {
                    $(this).closest('tr')
                        .find('.permission-checkbox')
                        .prop('checked', false)
                        .prop('disabled', true);
                    $("#all-permissions").prop('checked', false);
                }
            });

            $("#all-permissions").click(function(){
                if(this.checked){
                    $('input[type=checkbox]').prop('checked', true).removeAttr('disabled');;
                }else{
                    $('input[type=checkbox]').prop('checked', false);
                    $('.permission-is_create,.permission-is_update,.permission-is_delete').prop('disabled', true);
                }
            });

        });

        $(document).ready(function(){
            if('standard_user' === '{{ $userType }}') {
                $('.permissions-box').removeClass('hide').show();
            }
        });

        $('body')
            .on('change', '.user-type-select', function() {
                if('standard_user' === $(this).children('option:selected').val()) {
                    $('.permissions-box').removeClass('hide').show();
                } else {
                    $('.permissions-box').addClass('hide');
                }
            })
            .on('click', '#copy_permission', function(e) {
                e.preventDefault();
                let userId = $(this).closest('.copy-permission-box').find('#permissions_copied_from').children('option:selected').val();

                if(userId != '') {
                    $.get('{{ url('/') }}' + '/users/' + userId + '/permissions', function (data) {
                        $(".permission-checkbox").attr("checked", false);
                        for (let i = 0; i < data.length; i++) {
                            let item = data[i];
                            if (item.is_view) {
                                $('.permission-checkbox[name="permissions[' + item.module + '][is_view]"]')
                                        .prop('checked', true)
                                        .closest('tr').find('.permission-checkbox').removeAttr('disabled');
                            } else {
                                $('.permission-checkbox[name="permissions[' + item.module + '][is_view]"]')
                                        .prop('checked', false)
                                        .closest('tr').find('.permission-checkbox').prop('checked', false).prop('disabled', true);
                            }
                            if (item.is_create) {
                                $('.permission-checkbox[name="permissions[' + item.module + '][is_create]"]').prop('checked', true);
                            } else {
                                $('.permission-checkbox[name="permissions[' + item.module + '][is_create]"]').prop('checked', false);
                            }
                            if (item.is_update) {
                                $('.permission-checkbox[name="permissions[' + item.module + '][is_update]"]').prop('checked', true);
                            } else {
                                $('.permission-checkbox[name="permissions[' + item.module + '][is_update]"]').prop('checked', false);
                            }
                            if (item.is_delete) {
                                $('.permission-checkbox[name="permissions[' + item.module + '][is_delete]"]').prop('checked', true);
                            } else {
                                $('.permission-checkbox[name="permissions[' + item.module + '][is_delete]"]').prop('checked', false);
                            }
                        }

                    });
                }else{
                    alertify.error('{{ trans('fi.please_select_user') }}', 5);
                }
            });
    </script>
    @include('users._js_initials_colorpicker')

    @if ($editMode == true)
        {!! Form::model($user, ['route' => ['users.update', $user->id]]) !!}
    @else
        {!! Form::open(['route' => ['users.store']]) !!}
    @endif

    <section class="content-header">
        <h1 class="pull-left">
            {{ trans('fi.user_form') }}
        </h1>
        <div class="pull-right">
            @if ($returnUrl)
                <a href="{{ $returnUrl }}" class="btn btn-default"><i
                            class="fa fa-backward"></i> {{ trans('fi.back') }}</a>
            @endif
            <button class="btn btn-primary"><i class="fa fa-save"></i> {{ trans('fi.save') }}</button>
        </div>
        <div class="clearfix"></div>
    </section>

    <section class="content">

        @include('layouts._alerts')
        <div class="row">
            <div class="col-md-12">
                <div class="nav-tabs-custom">
                    <ul class="nav nav-tabs" id="setting-tabs">
                        <li class="active"><a data-toggle="tab" href="#tab-general">{{ trans('fi.general') }}</a></li>
                        <li><a data-toggle="tab" href="#tab-dashboard">{{ trans('fi.dashboard') }}</a></li>
                    </ul>
                    <div class="tab-content">

                        <div id="tab-general" class="tab-pane active">
                            <!-- General tab start -->
                            <div class="row">

                                <div class="col-md-12">

                                    <div class="box box-primary">

                                        <div class="box-body">

                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>{{ trans('fi.name') }}: </label>
                                                        {!! Form::text('name', null, ['id' => 'name', 'class' => 'form-control']) !!}
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>{{ trans('fi.email') }}: </label>
                                                        {!! Form::text('email', null, ['id' => 'email', 'class' => 'form-control']) !!}
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label>{{ trans('fi.initials') }}: </label>
                                                        {!! Form::text('initials', null, ['id' => 'initials', 'class' => 'form-control', 'maxlength' => 2]) !!}
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label>{{ trans(('fi.initials_bg_color')) }}: </label>
                                                        <div class="input-group fi-colorpicker colorpicker-element">
                                                            {!! Form::text('initials_bg_color', null, ['class' => 'form-control initials-bg-color', 'readonly' => true]) !!}
                                                            <div class="input-group-addon">
                                                                <i></i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            @if (!$editMode)
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>{{ trans('fi.password') }}: </label>
                                                        {!! Form::password('password', ['id' => 'password', 'class' => 'form-control']) !!}
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>{{ trans('fi.password_confirmation') }}: </label>
                                                        {!! Form::password('password_confirmation', ['id' => 'password_confirmation',
                                                    'class' => 'form-control']) !!}
                                                    </div>
                                                </div>
                                            </div>
                                            @endif

                                            <div class="row">
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label>{{ trans('fi.user_role') }}: </label>
                                                        {!! Form::select('user_type', $userTypes, $userType, ['id' => 'user_type', 'class' => 'form-control user-type-select']) !!}
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label>{{ trans('fi.status') }}: </label>
                                                        {!! Form::select('status', $status, null, ['id' => 'status', 'class' => 'form-control', isset($user->id) && auth()->user()->id == $user->id ? 'disabled' : '']) !!}
                                                    </div>
                                                </div>
                                            </div>

                                        </div>

                                    </div>

                                    <div class="box box-primary permissions-box hide">

                                        <div class="box-header">
                                            <div class="pull-left">
                                                <h3 class="box-title">{{ trans('fi.permissions') }}</h3>
                                            </div>
                                            <div class="pull-right">
                                                <div class="form-inline">

                                                    <div class="copy-permission-box form-group">
                                                        <label>{{ trans('fi.copy_from') }}: </label>
                                                        {!! Form::select('permissions_copied_from', $permissionsCopiedFrom, null, ['id' => 'permissions_copied_from', 'class' => 'form-control copy-permission-select']) !!}
                                                        <button class="btn btn-default btn-copy-permissions" id="copy_permission"><i class="fa fa-copy"></i> {{ trans('fi.copy') }}</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="box-body">

                                            <div class="form-group row">
                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <input id="all-permissions" type="checkbox">
                                                        <label for="all-permissions">{{ trans('fi.select_all_permissions') }} </label>
                                                    </div>
                                                    <table class="table table-hover table-striped">
                                                        <thead>
                                                        <tr>
                                                            <th class="vertical-header-20">{{ trans('fi.modules') }}</th>
                                                            @foreach($permissibleItems['modules'][0]['actions'] as $action)
                                                                <th class="hidden-sm hidden-xs">{{ trans('fi.' . $action) }}</th>
                                                            @endforeach
                                                            <th>{{ trans('fi.check-all') }}</th>
                                                        </tr>
                                                        </thead>
                                                        <tbody>
                                                        @foreach($permissibleItems['modules'] as $module)
                                                            <tr>
                                                                <td class="vertical-header-20">{{ $module['name'] }}</td>
                                                                @foreach($module['actions'] as $action)
                                                                    <td>{!! Form::checkbox('permissions[' . $module['slug'] . '][' . $action . ']', true, (1 == ($permissions[$module['slug']][$action] ?? 0)), ['class' => 'permission-checkbox permission-' . $action]) !!}</td>
                                                                @endforeach
                                                                <td class="vertical-header-20">
                                                                    {!! Form::checkbox('check-all', true, null, ['class' => 'check-all']) !!}
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                        </tbody>
                                                    </table>
                                                    <hr>
                                                    <table class="table table-hover table-striped">
                                                        <thead>
                                                        <tr>
                                                            <th class="vertical-header-20">{{ trans('fi.reports') }}</th>
                                                            @foreach($permissibleItems['reports'][0]['actions'] as $action)
                                                                <th class="hidden-sm hidden-xs">{{ trans('fi.' . $action) }}</th>
                                                            @endforeach
                                                        </tr>
                                                        </thead>
                                                        <tbody>
                                                        @foreach($permissibleItems['reports'] as $report)
                                                            <tr>
                                                                <td class="vertical-header-20">{{ $report['name'] }}</td>
                                                                @foreach($report['actions'] as $action)
                                                                    <td>{!! Form::checkbox('permissions[' . $report['slug'] . '][' . $action . ']', true, (1 == ($permissions[$report['slug']][$action] ?? 0)), ['class' => 'reports']) !!}</td>
                                                                @endforeach
                                                            </tr>
                                                        @endforeach
                                                        </tbody>
                                                    </table>
                                                    <hr>
                                                    <table class="table table-hover table-striped">
                                                        <thead>
                                                        <tr>
                                                            <th class="vertical-header-20">{{ trans('fi.dashboards') }}</th>
                                                            @foreach($permissibleItems['dashboards'][0]['actions'] as $action)
                                                                <th class="hidden-sm hidden-xs">{{ trans('fi.' . $action) }}</th>
                                                            @endforeach
                                                        </tr>
                                                        </thead>
                                                        <tbody>
                                                        @foreach($permissibleItems['dashboards'] as $dashboard)
                                                            <tr>
                                                                <td class="vertical-header-20">{{ $dashboard['name'] }}</td>
                                                                @foreach($dashboard['actions'] as $action)
                                                                    <td>{!! Form::checkbox('permissions[' . $dashboard['slug'] . '][' . $action . ']', true, (1 == ($permissions[$dashboard['slug']][$action] ?? 0)), ['class' => 'dashboards']) !!}</td>
                                                                @endforeach
                                                            </tr>
                                                        @endforeach
                                                        </tbody>
                                                    </table>
                                                    @if(isset($enabledAddons) && $enabledAddons->count() > 0)
                                                    <hr>
                                                    <table class="table table-hover table-striped">
                                                        <thead>
                                                        <tr>
                                                            <th class="vertical-header-20">{{ trans('fi.addons') }}</th>
                                                            @foreach($permissibleItems['addons'][$enabledAddons[0]->name]['actions'] as $action)
                                                                <th class="hidden-sm hidden-xs">{{ trans('fi.' . $action) }}</th>
                                                            @endforeach
                                                            <th>{{ trans('fi.check-all') }}</th>
                                                        </tr>
                                                        </thead>
                                                        <tbody>
                                                        @foreach($enabledAddons as $addon)
                                                            @if(isset($permissibleItems['addons'][$addon->name]) && $permissibleItems['addons'][$addon->name]['name'] == $addon->name)
                                                            <tr>
                                                                <td class="vertical-header-20">{{ $permissibleItems['addons'][$addon->name]['name'] }}</td>
                                                                @foreach($permissibleItems['addons'][$addon->name]['actions'] as $action)
                                                                    <td>{!! Form::checkbox('permissions[' . $permissibleItems['addons'][$addon->name]['slug'] . '][' . $action . ']', true, (1 == ($permissions[$permissibleItems['addons'][$addon->name]['slug']][$action] ?? 0)), ['class' => 'addons permission-checkbox permission-' . $action]) !!}</td>
                                                                @endforeach
                                                                <td class="vertical-header-20">
                                                                    {!! Form::checkbox('check-all', true, null, ['class' => 'check-all']) !!}
                                                                </td>
                                                            </tr>
                                                            @endif
                                                        @endforeach
                                                        </tbody>
                                                    </table>
                                                    @endif
                                                </div>
                                            </div>

                                        </div>

                                    </div>

                                    @if ($customFields)
                                        <div class="box box-primary">

                                            <div class="box-header">
                                                <h3 class="box-title">{{ trans('fi.custom_fields') }}</h3>
                                            </div>

                                            <div class="box-body">

                                                @include('custom_fields._custom_fields_unbound', ['object' => isset($user) ? $user : []])

                                            </div>

                                        </div>
                                    @endif

                                </div>

                            </div>
                            <!-- General tab end -->
                        </div>
                        <div id="tab-dashboard" class="tab-pane">
                            @foreach ($dashboardWidgets as $widget)
                            @section('trasnlateWidgetNamesSection')
                                @switch(strtolower($widget))
                                @case('invoicesummary')
                                {{ $widgetIcon = '<i class="fa fa-file-text"></i>' }}
                                {{ $widgetHdr = trans('fi.invoice_summary') }}
                                @break
                                @case('quotesummary')
                                {{ $widgetIcon = '<i class="fa fa-file-text-o"></i>' }}
                                {{ $widgetHdr = trans('fi.quote_summary') }}
                                @break
                                @case('clientactivity')
                                {{ $widgetIcon = '<i class="fa fa-child"></i>' }}
                                {{ $widgetHdr = trans('fi.recent_client_activity') }}
                                @break
                                @case('tasks')
                                {{ $widgetIcon = '<i class="fa fa-list"></i>' }}
                                {{ $widgetHdr = trans('fi.task_list') }}
                                @break
                                @case('clienttimeline')
                                {{ $widgetIcon = '<i class="fa fa-list"></i>' }}
                                {{ $widgetHdr = trans('fi.client_timeline') }}
                                @break
                                @default
                                {{ $widgetIcon = '' }}
                                {{ $widgetHdr = $widget }}
                                @endswitch
                            @endsection

                            <h4 style="font-weight: bold; clear: both; margin-top: 20px;"> {!! $widgetIcon !!} {{ $widgetHdr }}</h4>
                            <hr style="margin-top: 0; margin-bottom: 10px; border-top: 1px solid #d3d3d3;">
                            <div class="row">

                                <div class="col-md-2" style="margin-left: 10px;">
                                    <div class="form-group">
                                        <label>{{ trans('fi.enabled') }}: </label>
                                        {!! Form::select('setting[widgetEnabled' . $widget . ']', $yesNoArray, config('fi.widgetEnabled' .
                                        $widget), ['id' => 'widgetEnabled' . $widget, 'class' => 'form-control']) !!}
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>{{ trans('fi.display_order') }}: </label>
                                        {!! Form::select('setting[widgetDisplayOrder' . $widget . ']', $displayOrderArray,
                                        config('fi.widgetDisplayOrder' . $widget),
                                        ['id' => 'widgetDisplayOrder' . $widget, 'class' => 'form-control']) !!}
                                    </div>
                                </div>
                                @if (strtolower($widget) == 'tasks' || strtolower($widget) == 'clienttimeline')
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label>{{ trans('fi.column_width') }}: </label>
                                            {!! Form::select('setting[widgetColumnWidth' . $widget . ']', ['6'=>6,'8'=>8,'12'=>12],
                                            config('fi.widgetColumnWidth' . $widget) ? config('fi.widgetColumnWidth' . $widget) :  6, ['id' => 'widgetColumnWidth' . $widget, 'class' =>
                                            'form-control']) !!}
                                        </div>
                                    </div>
                                @else
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label>{{ trans('fi.column_width') }}: </label>
                                            {!! Form::select('setting[widgetColumnWidth' . $widget . ']', $colWidthArray,
                                            config('fi.widgetColumnWidth' . $widget) ? config('fi.widgetColumnWidth' . $widget) :  6, ['id' => 'widgetColumnWidth' . $widget, 'class' =>
                                            'form-control']) !!}
                                        </div>
                                    </div>
                                @endif

                                @if (strtolower($widget) == 'tasks')
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label>{{ trans('fi.display_profile_image') }}: </label>
                                            {!! Form::select('setting[displayProfileImage]', $yesNoArray, config('fi.displayProfileImage'), ['class' => 'form-control']) !!}
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label>{{ trans('fi.include_time_in_due_date') }}: </label>
                                            {!! Form::select('setting[includeTimeInTaskDueDate]', $yesNoArray, config('fi.includeTimeInTaskDueDate'), ['class' => 'form-control']) !!}
                                        </div>
                                    </div>
                                @endif

                            </div>

                            @if (view()->exists($widget . 'WidgetSettings'))
                                @include($widget . 'WidgetSettings')
                            @endif

                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </section>

    {!! Form::close() !!}
@stop