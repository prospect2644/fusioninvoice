@extends('layouts.master')

@section('javascript')
    @include('tasks._js_index')
@stop

@section('content')

    <section class="content-header">
        <h1 class="fa fa-tasks pull-left"> </h1>
        <h1 class="pull-left">
            {{ trans('fi.tasks') }}
        </h1>

        <div class="btn-group" style="flex-grow: 100;">
            {!! Form::open(['method' => 'GET', 'id' => 'filter', 'class' => 'form-inline']) !!}
            <div class="pull-left" style="padding-left:20px;">
                @if (isset($searchPlaceholder))
                    <div class="input-group">
                        {!! Form::text('search', request('search'), ['id' => 'search', 'class' => 'form-control inline input-sm','autofocus','placeholder' => $searchPlaceholder]) !!}
                        <span class="input-group-btn">
                            <button type="submit" id="search-btn" class="btn btn-flat btn-sm btn-primary"><i class="fa fa-search"></i></button>
                        </span>
                    </div>
                @endif
            </div>
            <div class="pull-right" style="padding-left:25px;">
                {!! Form::select('status', $statuses, request('status', 'open'),['class' => 'task-status form-control inline input-sm']) !!}
                {!! Form::text('date_range_filter', null, ['id' => 'date_range_filter', 'class' => 'form-control input-sm', 'placeholder' => trans('fi.date_range')]) !!}
                {!! Form::hidden('date_range_filter_from', null, ['id' => 'date_range_filter_from', 'class' => 'form-control input-sm']) !!}
                {!! Form::hidden('date_range_filter_to', null, ['id' => 'date_range_filter_to', 'class' => 'form-control input-sm']) !!}
            </div>
            {!! Form::close() !!}
        </div>
        <div class="pull-right">
            <a href="{{ route('task.create') }}" class="btn btn-primary"><i class="fa fa-plus"></i> {{ trans('fi.new') }}</a>
        </div>
        <div class="clearfix"></div>
    </section>

    <section class="content">

        <div class="row">

            <div class="col-xs-12">

                <div class="box box-primary">

                    <div class="box-body no-padding">
                        @include('tasks._table', ['bulk_action' => true])
                    </div>

                </div>

                <div class="pull-left">
                    @if(request('date_range_filter') || (request('status') && request('status') != 'all') || request('search'))
                        <i class="fa fa-filter"></i> {{ trans('fi.n_records_match', ['label' => $tasks->total(),'plural' => $tasks->total() > 1 ? 's' : '']) }}
                        <button type="button" class="btn btn-link" id="btn-clear-filters">
                            {{ trans('fi.clear') }}
                        </button>
                    @endif
                </div>

                <div class="pull-right">
                    {!! $tasks->appends(request()->except('page'))->render() !!}
                </div>

            </div>

        </div>

    </section>

@stop