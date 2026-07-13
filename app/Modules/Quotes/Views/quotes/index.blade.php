@extends('layouts.master')

@section('javascript')
    @include('quotes._js_index')
@stop

@section('content')

    <section class="content-header" style="display: flex;">
        <h1 class="fa fa-file-text-o pull-left"> </h1>
        <h1 class="pull-left">{{ trans('fi.quotes') }}</h1>

        <div class="btn-group" style="flex-grow: 100;">
            {!! Form::open(['method' => 'GET', 'id' => 'filter', 'class' => 'form-inline']) !!}

            <div class="pull-left" style="padding-left:20px;padding-right:30px;">
                @if (isset($searchPlaceholder))
                    <div class="input-group">
                        {!! Form::text('search', request('search'), ['id' => 'search', 'class' => 'form-control inline','autofocus','placeholder' => $searchPlaceholder]) !!}
                        <span class="input-group-btn">
                            <button type="submit" id="search-btn" class="btn btn-flat btn-primary"><i class="fa fa-search"></i></button>
                        </span>
                    </div>
                @endif
            </div>
            
            <div class="pull-left" style="padding-right:25px;">
                @can('quotes.update')
                <div class="btn-group bulk-actions">
                    <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                        {{ trans('fi.change_status') }} <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu">
                        @foreach ($bulkStatuses as $key => $status)
                            <li><a href="javascript:void(0)" class="bulk-change-status" data-status="{{ $key }}">{{ $status }}</a></li>
                        @endforeach
                    </ul>
                </div>
                @endcan

                @can('quotes.delete')
                <a href="javascript:void(0)" class="btn btn-danger bulk-actions" id="btn-bulk-delete"><i class="fa fa-trash"></i> {{ trans('fi.delete') }}</a>
                @endcan
                @can('quotes.view')
                <a href="javascript:void(0)" class="btn btn-primary bulk-actions" id="btn-bulk-pdf"><i class="fa fa-file-pdf-o"></i> {{ trans('fi.pdf') }}</a>
                <a href="javascript:void(0)" class="btn btn-primary bulk-actions" id="btn-bulk-print"><i class="fa fa-print"></i> {{ trans('fi.print') }}</a>
                @endcan
            </div>

            <div class="pull-right">
                {!! Form::select('company_profile', $companyProfiles, request('company_profile'), ['class' => 'quote_filter_options form-control inline']) !!}
                {!! Form::select('status', $filterStatuses, request('status'), ['class' => 'quote_filter_options form-control inline']) !!}
            </div>

            {!! Form::close() !!}
        </div>
    
        <div class="pull-right" style="text-align: right;padding-left:15px;">
            @can('quotes.create')
            <a href="javascript:void(0)" class="btn btn-primary create-quote"><i class="fa fa-plus"></i> {{ trans('fi.new') }}</a>
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
                        @include('quotes._table', ['bulk_action' => true])
                    </div>

                </div>

                <div class="pull-left">
                    @if((request('company_profile') || request('status') || request('search')) && request('status') != 'all')
                        <i class="fa fa-filter"></i> {{ trans('fi.n_records_match', ['label' => $quotes->total(),'plural' => $quotes->total() > 1 ? 's' : '']) }}
                        <button type="button" class="btn btn-link" id="btn-clear-filters">{{ trans('fi.clear') }}</button>
                    @endif
                </div>

                <div class="pull-right">
                    {!! $quotes->appends(request()->except('page'))->render() !!}
                </div>

            </div>

        </div>

    </section>

@stop