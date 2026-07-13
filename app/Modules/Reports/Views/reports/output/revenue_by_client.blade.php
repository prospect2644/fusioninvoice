@extends('reports.layouts.master')
@section('title')
    {{ config('fi.headerTitleText') }} | {{ trans('fi.revenue_by_client') }}
@stop
@section('content')

    <h1 style="text-align: center;">{{ trans('fi.revenue_by_client') }}</h1>

    <table class="alternate">
        <thead>
        <tr>
            <th>{{ trans('fi.client') }}</th>
            <th>{{ trans('fi.year') }}</th>
            @foreach ($months as $month)
                <th class="amount">{{ $month }}</th>
            @endforeach
            <th class="amount">{{ trans('fi.total') }}</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($results['clients'] as $client => $year)
            @foreach ($year as $key => $amounts)
                <tr>
                    <td>{{ $client }}</td>
                    <td>{{ $key }}</td>
                    @foreach (array_keys($months) as $monthKey)
                        <td class="amount">{{ $amounts['months'][$monthKey] }}</td>
                    @endforeach
                    <td class="amount">{{ $amounts['total'] }}</td>
                </tr>
            @endforeach
        @endforeach
        <tr>
            <td colspan="14" class="amount"><strong>{{ trans('fi.total') }}</strong></td>
            <td class="amount"><strong>{{ $results['grand_total'] }}</strong></td>
        </tr>
        </tbody>
    </table>

@stop