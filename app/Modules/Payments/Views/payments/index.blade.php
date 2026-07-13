@extends('layouts.master')

@section('javascript')
    @include('payments._js_index')
@stop

@section('content')

    <section class="content-header">
        <h1 class="fa fa-credit-card pull-left"> </h1>
        <h1 class="pull-left">{{ trans('fi.payments') }}</h1>
        <div class="btn-group" style="flex-grow: 100;">
            <div class="pull-left" style="padding-left:20px;padding-right:30px;">
                @if (isset($searchPlaceholder))
                    {!! Form::open(['method' => 'GET', 'id' => 'filter', 'class' => 'form-inline']) !!}
                        <div class="input-group">
                            {!! Form::text('search', request('search'), ['id' => 'search', 'class' => 'form-control inline','autofocus','placeholder' => $searchPlaceholder]) !!}
                            <span class="input-group-btn">
                                <button type="submit" id="search-btn" class="btn btn-flat btn-primary"><i class="fa fa-search"></i></button>
                            </span>
                        </div>
                    {!! Form::close() !!}
                @endif
            </div>
            <div class="pull-left" style="padding-right:25px;">
                @can('payments.delete')
                <div class="pull-right">
                    <a href="javascript:void(0)" class="btn btn-danger bulk-actions" id="btn-bulk-delete"><i class="fa fa-trash"></i> {{ trans('fi.delete') }}</a>
                </div>
                @endcan
            </div>
        </div>

        <div class="pull-right" style="text-align: right;padding-left:15px;">
            @can('payments.create')
                <a href="javascript:void(0)" class="btn btn-primary create-payment">
                    <i class="fa fa-plus"></i> {{ trans('fi.new') }}
                </a>
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
                        @include('payments._table', ['bulk_action' => true])
                    </div>

                </div>

                <div class="pull-left">
                    @if(request('search'))
                        <i class="fa fa-filter"></i> {{ trans('fi.n_records_match', ['label' => $payments->total(),'plural' => $payments->total() > 1 ? 's' : '']) }}
                        <button type="button" class="btn btn-link" id="btn-clear-filters">{{ trans('fi.clear') }}</button>
                    @endif
                </div>

                <div class="pull-right">
                    {!! $payments->appends(request()->except('page'))->render() !!}
                </div>

            </div>

        </div>

    </section>

@stop