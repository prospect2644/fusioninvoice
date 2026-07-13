@extends('layouts.master')

@section('head')
    @include('layouts._select2')
@stop

@section('content')

    <script type="text/javascript">
        $(function () {
            $('#name').focus();

            // Define the select settings
            var settings = {
                placeholder: '{{ trans('fi.select-item-category') }}',
                allowClear: true,
                tags: true,
                selectOnClose: true
            };

            // Make all existing items select
            $('.category-lookup').select2(settings);

        });
    </script>

    @if ($editMode == true)
        {!! Form::model($itemLookup, ['route' => ['itemLookups.update', $itemLookup->id]]) !!}
    @else
        {!! Form::open(['route' => 'itemLookups.store']) !!}
    @endif

    <section class="content-header">
        <h1 class="pull-left">
            {{ trans('fi.item_lookup_form') }}
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
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="">{{ trans('fi.name') }}: </label>
                                {!! Form::text('name', null, ['id' => 'name', 'class' => 'form-control']) !!}
                            </div>

                            <div class="form-group">
                                <label>{{ trans('fi.category') }}: </label>
                                {!! Form::select('category_name', $itemCategory, null, ['id' => 'category_name', 'class' => 'form-control category-lookup']) !!}
                            </div>

                            <div class="form-group">
                                <label class="">{{ trans('fi.description') }}: </label>
                                {!! Form::textarea('description', null, ['id' => 'description', 'class' => 'form-control', 'rows' => 3]) !!}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="">{{ trans('fi.price') }}: </label>
                                {!! Form::text('price', (($editMode) ? $itemLookup->formatted_numeric_price: null), ['id' => 'price', 'class' => 'form-control']) !!}
                            </div>

                            <div class="form-group">
                                <label class="">{{ trans('fi.tax_1') }}: </label>
                                {!! Form::select('tax_rate_id', ['-1' => trans('fi.system_default')] + $taxRates, $editMode == true ? null : -1, ['class' => 'form-control']) !!}
                            </div>

                            <div class="form-group">
                                <label class="">{{ trans('fi.tax_2') }}: </label>
                                {!! Form::select('tax_rate_2_id', ['-1' => trans('fi.system_default')] + $taxRates, $editMode == true ? null : -1, ['class' => 'form-control']) !!}
                            </div>
                        </div>

                    </div>

                </div>

            </div>

        </div>

    </section>

    {!! Form::close() !!}
@stop