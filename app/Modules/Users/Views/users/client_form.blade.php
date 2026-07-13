@extends('layouts.master')

@section('javascript')

    @include('layouts._datepicker')
    @include('layouts._colorpicker')
    @include('layouts._select2')

@stop

@section('content')

    <script type="text/javascript">
        $(function () {

            $('#client_id').change(function () {
                $.post('{{ route('users.clientInfo') }}', {
                    id: $('#client_id').val()
                }).done(function (response) {
                    $('#name').val(response.unique_name).change();
                    $('#email').val(response.email);
                });
            });

            $('#btn-delete-custom-img').click(function () {
                var url = "{{ route('users.deleteImage', [isset($user->id) ? $user->id : '','field_name' => '']) }}";
                $.post(url + '/' + $(this).data('field-name')).done(function () {
                    $('.custom_img').html('');
                });
            });

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
            {{ trans('fi.client') . ' ' . trans('fi.user_form') }}
        </h1>
        <div class="pull-right">
            <button class="btn btn-primary"><i class="fa fa-save"></i> {{ trans('fi.save') }}</button>
        </div>
        <div class="clearfix"></div>
    </section>

    <section class="content">

        @include('layouts._alerts')

        <div class="row">

            <div class="col-md-12">

                <div class="box box-primary">

                    <div class="box-body">

                        @if (!$editMode)
                            <div class="form-group">
                                <label>{{ trans('fi.client') }}:</label>
                                {!! Form::select('client_id', ['' => ''] + $clients, null, ['class' => 'form-control', 'id' => 'client_id']) !!}
                            </div>
                        @endif

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{ trans('fi.name') }}: </label>
                                    {!! Form::text('name', null, ['id' => 'name', 'class' => 'form-control', 'readonly' => 'readonly']) !!}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{ trans('fi.email') }}: </label>
                                    {!! Form::text('email', null, ['id' => 'email', 'class' => 'form-control', 'readonly' => 'readonly']) !!}
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>{{ trans('fi.initials') }}: </label>
                                    {!! Form::text('initials', null, ['id' => 'initials', 'class' => 'form-control']) !!}
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>{{ trans(('fi.initials_bg_color')) }}: </label>
                                    <div class="input-group fi-colorpicker colorpicker-element">
                                        {!! Form::text('initials_bg_color', null, ['class' => 'form-control initials-bg-color']) !!}
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
                            {!! Form::hidden('user_type', $userType) !!}
                        @endif

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

    </section>

    {!! Form::close() !!}
@stop