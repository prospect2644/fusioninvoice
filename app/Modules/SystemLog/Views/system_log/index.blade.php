@extends('layouts.master')

@section('content')

    <section class="content-header">
        <h1 class="pull-left">
            {{ trans('fi.system_log') }}
        </h1>

        <div class="clearfix"></div>
    </section>

    <section class="content">

        @include('layouts._alerts')

        <div class="row">
            <div class="col-md-12">

                <textarea rows="24" cols="154" readonly="readonly" disabled>{{ $logs }}</textarea>

            </div>

        </div>

    </section>

@stop