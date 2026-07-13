@extends('layouts.master')

@section('content')

    <script type="text/javascript">
        $(function () {
            $('#name').focus();
        });
    </script>

    @if ($editMode == true)
        {!! Form::model($documentNumberScheme, ['route' => ['documentNumberSchemes.update', $documentNumberScheme->id]]) !!}
    @else
        {!! Form::open(['route' => 'documentNumberSchemes.store']) !!}
    @endif

    <section class="content-header">
        <h1 class="pull-left">
            {{ trans('fi.document_number_scheme_form') }}
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

                        <div class="form-group">
                            <label>{{ trans('fi.name') }}: </label>
                            {!! Form::text('name', null, ['id' => 'name', 'class' => 'form-control']) !!}
                        </div>

                        <div class="form-group">
                            <label>{{ trans('fi.type') }}: </label>
                            {!! Form::select('type', $types, request('status'), ['class' => 'form-control']) !!}
                        </div>

                        <div class="form-group">
                            <label>{{ trans('fi.format') }}: </label>
                            {!! Form::text('format', null, ['id' => 'format', 'class' => 'form-control']) !!}
                            <span class="help-block">{{ trans('fi.available_fields') }}: {NUMBER} {YEAR} {MONTH} {MONTHSHORTNAME} {WEEK} {INVOICE_PREFIX}</span>
                        </div>

                        <div class="form-group">
                            <label>{{ trans('fi.next_number') }}: </label>
                            {!! Form::text('next_id', isset($documentNumberScheme->next_id) ? $documentNumberScheme->next_id : 1, ['id' => 'next_id', 'class' => 'form-control']) !!}
                        </div>

                        <div class="form-group">
                            <label>{{ trans('fi.left_pad') }}: </label>
                            {!! Form::text('left_pad', isset($documentNumberScheme->left_pad) ? $documentNumberScheme->left_pad : 0, ['id' => 'left_pad', 'class' => 'form-control']) !!}
                            <span class="help-block">{{ trans('fi.left_pad_description') }}</span>
                        </div>

                        <div class="form-group">
                            <label>{{ trans('fi.reset_number') }}: </label>
                            {!! Form::select('reset_number', $resetNumberOptions, null, ['id' => 'reset_number', 'class' => 'form-control']) !!}
                        </div>

                    </div>

                </div>

            </div>

        </div>

    </section>

    {!! Form::close() !!}
@stop