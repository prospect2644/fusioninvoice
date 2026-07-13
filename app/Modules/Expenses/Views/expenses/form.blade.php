@extends('layouts.master')

@section('head')
    @include('layouts._datepicker')
    @include('layouts._select2')
    @include('expenses._js_vendor_lookup')
    @include('expenses._js_category_lookup')
    @include('clients._js_lookup')
@stop

@section('javascript')
    <script type="text/javascript">
        $(function () {

            $('#expense_date').datepicker({format: '{{ config('fi.datepickerFormat') }}', autoclose: true});

            @if ($editMode == true)
                $('#btn-copy-expense').click(function () {
                    $.post("{{ route('expenseCopy.store') }}", {
                        expense_id: "{{ isset($expense->id) ? $expense->id : '' }}"
                    }).done(function (response) {
                        window.location = '{{ url('expenses') }}' + '/' + response.id + '/edit';
                    }).fail(function (response) {
                        showAlertifyErrors($.parseJSON(response.responseText).errors);
                    });
                });
            @endif
        });

        $('#btn-delete-custom-img').click(function () {
            var url = "{{ route('expenses.deleteImage', [isset($expense->id) ? $expense->id : '','field_name' => '']) }}";
            $.post(url + '/' + $(this).data('field-name')).done(function () {
                $('.custom_img').html('');
            });
        });

    </script>
@stop

@section('content')

    @if ($editMode == true)
        {!! Form::model($expense, ['route' => ['expenses.update', $expense->id], 'files' => true]) !!}
    @else
        {!! Form::open(['route' => 'expenses.store', 'files' => true]) !!}
    @endif

    {!! Form::hidden('user_id', auth()->user()->id) !!}

    <section class="content-header">
        <h1 class="pull-left">
            @if ($editMode == true)
                {{ trans('fi.expense') }} #{{ $expense->id }}    
            @else
                {{ trans('fi.expense_form') }}                    
            @endif
        </h1>
        <div class="pull-right">
            <div class="btn-group">
                <a href="{{ route('expenses.index') }}" class="btn btn-default"><i class="fa fa-backward"></i> {{ trans('fi.back') }}</a>
            </div>
            @if ($editMode == true)
                @can('expenses.create')
                <div class="btn-group">
                    <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                        {{ trans('fi.other') }} <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-right" role="menu">
                        <li><a href="#" id="btn-copy-expense"><i
                                        class="fa fa-copy"></i> {{ trans('fi.copy') }}</a></li>
                    </ul>
                </div>
                @endcan
            @endif
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

                        <div class="row">

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>* {{ trans('fi.company_profile') }}: </label>
                                    {!! Form::select('company_profile_id', $companyProfiles, (($editMode) ? $expense->company_profile_id : config('fi.defaultCompanyProfile')), ['id' => 'company_profile_id', 'class' => 'form-control']) !!}
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>* {{ trans('fi.date') }}: </label>
                                    {!! Form::text('expense_date', (($editMode) ? $expense->formatted_expense_date : $currentDate), ['id' => 'expense_date', 'class' => 'form-control']) !!}
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>* {{ trans('fi.category') }}: </label>
                                    {!! Form::select('category_name', $expenseCategory, null, ['id' => 'category_name', 'class' => 'form-control category-lookup']) !!}
                                </div>
                            </div>

                        </div>

                        <div class="row">

                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>* {{ trans('fi.amount') }}: </label>
                                    {!! Form::text('amount', (($editMode) ? $expense->formatted_numeric_amount : null), ['id' => 'amount', 'class' => 'form-control']) !!}
                                </div>
                            </div>

                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>{{ trans('fi.tax') }}: </label>
                                    {!! Form::text('tax', (($editMode) ? $expense->formatted_numeric_tax : null), ['id' => 'tax', 'class' => 'form-control']) !!}
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>{{ trans('fi.vendor') }}: </label>
                                    {!! Form::select('vendor_name', $vendors, null, ['id' => 'vendor_name', 'class' => 'form-control vendor-lookup']) !!}
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>{{ trans('fi.client') }}: </label>
                                    {!! Form::select('client_name', $clients, null, ['id' => 'client_name', 'class' => 'form-control client-lookup']) !!}
                                </div>
                            </div>

                        </div>

                        <div class="form-group">
                            <label>{{ trans('fi.description') }}: </label>
                            {!! Form::textarea('description', null, ['id' => 'description', 'class' => 'form-control']) !!}
                        </div>

                        @if (!$editMode)
                            @if (!config('app.demo'))
                                @can('attachments.create')
                                <div class="form-group">
                                    <label>{{ trans('fi.attach_files') }}: </label>
                                    {!! Form::file('attachments[]', ['id' => 'attachments', 'class' => 'form-control', 'multiple' => 'multiple']) !!}
                                </div>
                                @endcan
                            @endif
                        @else
                            @include('attachments._table', ['object' => $expense, 'model' => 'FI\Modules\Expenses\Models\Expense'])
                        @endif

                        @if ($customFields)
                            @include('custom_fields._custom_fields_unbound', ['object' => isset($expense) ? $expense : []])
                        @endif
                    </div>

                </div>

            </div>

        </div>

    </section>

    {!! Form::close() !!}
@stop