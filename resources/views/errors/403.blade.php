@extends('layouts.master')

@section('content')

    <section class="content-header"></section>

    <section class="content">
        <div class="error-page">
            <h2 class="headline text-yellow">404</h2>

            <div class="error-content">
                <h3><i class="fa fa-warning text-yellow"></i> {{ trans('fi.page_not_found') }}</h3>

                <p>
                    {!! trans('fi.return_to_dashboard', ['dashboard_link' => route('dashboard.index')]) !!}
                </p>
            </div>
        </div>
    </section>
@stop