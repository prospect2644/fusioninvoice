@extends('layouts.master')

@section('content')

    <script type="text/javascript">
        $(function () {
            alertify.defaults.theme.ok = "ui negative button";
            alertify.defaults.theme.cancel = "ui black button";

            $("<style>").text(".ajs-header{ background-color: #ba0606 !important; }").appendTo($("body"));

            $('.delete-item-looks').click(function () {

                var $_this = $(this);

                alertify.confirm("{!! trans('fi.delete_record_warning') !!}", function () {
                    window.location = decodeURIComponent($_this.data('action'));
                }, function () {
                    alertify.alert().destroy();
                }).setHeader(confirmHeader).set({transition: 'zoom', defaultFocus: 'cancel'});

            });

            $('#btn-clear-filters').click(function () {
                $('#search').val('');
                $('.item_filter_options').prop('selectedIndex', 0);
                $('#filter').submit();
            });

            $('.item_filter_options').change(function () {
                $('form#filter').submit();
            });
        });
    </script>

    <section class="content-header">
        <h1 class="pull-left">
            {{ trans('fi.item_lookups') }}
        </h1>

        <div class="btn-group" style="flex-grow: 100;">
            {!! Form::open(['method' => 'GET', 'id' => 'filter', 'class' => 'form-inline']) !!}
            <div class="pull-left" style="padding-left:20px;">
                @if (isset($searchPlaceholder))
                    <div class="input-group">
                        {!! Form::text('search', request('search'), ['id' => 'search', 'class' => 'form-control inline input-sm','placeholder' => $searchPlaceholder]) !!}
                        <span class="input-group-btn">
                            <button type="submit" id="search-btn" class="btn btn-flat btn-sm btn-primary"><i class="fa fa-search"></i></button>
                        </span>
                    </div>
                @endif
            </div>
            <div class="pull-right" style="padding-left:25px;">
                {!! Form::select('category', $categories, request('category'), ['class' => 'item_filter_options form-control inline input-sm']) !!}
            </div>
            {!! Form::close() !!}
        </div>
        <div class="pull-right">
            @can('item_lookup.create')
            <a href="{{ route('itemLookups.create') }}" class="btn btn-primary"><i class="fa fa-plus"></i> {{ trans('fi.new') }}</a>
            @endcan
        </div>
        <div class="clearfix"></div>
    </section>

    <section class="content">

        @include('layouts._alerts')

        <div class="row">

            <div class="col-xs-12">

                <div class="box box-primary">

                    <div class="box-body no-padding">
                        <table class="table table-hover table-striped table-striped">

                            <thead>
                            <tr>
                                <th>{!! Sortable::link('name', trans('fi.name')) !!}</th>
                                <th>{!! Sortable::link('item_categories.name', trans('fi.category')) !!}</th>
                                <th>{!! Sortable::link('description', trans('fi.description')) !!}</th>
                                <th>{!! Sortable::link('price', trans('fi.price')) !!}</th>
                                <th>{{ trans('fi.tax_1') }}</th>
                                <th>{{ trans('fi.tax_2') }}</th>
                                <th class="text-right">{{ trans('fi.options') }}</th>
                            </tr>
                            </thead>

                            <tbody>
                            @foreach ($itemLookups as $itemLookup)
                                <tr>
                                    <td><a href="{{ route('itemLookups.edit', [$itemLookup->id]) }}">{{ $itemLookup->name }}</a></td>
                                    <td>{{ $itemLookup->category_name }}</td>
                                    <td>{{ $itemLookup->description }}</td>
                                    <td>{{ $itemLookup->formatted_price }}</td>
                                    <td>{{ $itemLookup->formatted_taxRate }}</td>
                                    <td>{{ $itemLookup->formatted_taxRate2 }}</td>
                                    <td align="right">
                                        <a href="#" data-action="{{ route('itemLookups.delete',[$itemLookup->id]) }}"
                                               class="btn btn-xs btn-danger delete-item-looks" title=" {{ trans('fi.delete') }}">
                                            <i class="fa fa-trash-o"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>

                        </table>
                    </div>

                </div>

                <div class="pull-left">
                    @if(request('category') || request('search'))
                        <i class="fa fa-filter"></i> {{ trans('fi.n_records_match', ['label' => $itemLookups->total(),'plural' => $itemLookups->total() > 1 ? 's' : '']) }}
                        <button type="button" class="btn btn-link" id="btn-clear-filters">{{ trans('fi.clear') }}</button>
                    @endif
                </div>

                <div class="pull-right">
                    {!! $itemLookups->appends(request()->except('page'))->render() !!}
                </div>

            </div>

        </div>

    </section>

@stop