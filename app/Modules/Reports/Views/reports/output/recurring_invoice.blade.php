@extends('reports.layouts.master')
@section('title')
    {{ config('fi.headerTitleText') }} | {{ trans('fi.recurring_invoice_list') }}
@stop
@section('content')

    <h1 style="margin-bottom: 0;">{{ trans('fi.recurring_invoice_list') }}</h1>
    <h3 style="margin-top: 0;">{{ $results['from_date'] }} - {{ $results['to_date'] }}</h3>
    @if(count($results['records']) > 0)
        @foreach ($results['records'] as $period => $period_wise_data)
            @foreach ($period_wise_data as $frequency => $frequency_wise_data)
                <h2>{{ trans('fi.every') }} {{ $frequency }} {{ $period }}</h2>
                <table class="alternate">
                    <thead>
                    <tr>
                        <th style="width: 10%; text-align: left;">{{ trans('fi.id') }}</th>
                        <th style="width: 26%; text-align: left;">{{ trans('fi.client') }}</th>
                        <th style="width: 26%; text-align: left;">{{ trans('fi.summary') }}</th>
                        <th style="width: 14%; text-align: left;">{{ trans('fi.next_date') }}</th>
                        <th style="width: 14%; text-align: left;">{{ trans('fi.stop_date') }}</th>
                        <th class="amount" style="width: 10%;">{{ trans('fi.total') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($frequency_wise_data as $item)
                        <tr>
                            <td>{{ $item['id'] }}</td>
                            <td>{{ $item['client_name'] }}</td>
                            <td>{{ $item['summary'] }}</td>
                            <td>{{ $item['next_date'] }}</td>
                            <td>{{ $item['stop_date'] }}</td>
                            <td align="right">{{ $item['total'] }}</td>
                        </tr>
                    @endforeach
                    <tr>
                        <td colspan="4" class="amount"><strong>{{ trans('fi.total') }}</strong></td>
                        <td class="amount">
                            <strong>{{ trans('fi.invoices') }} {{ $results['total_invoice'][$period][$frequency] }}</strong>
                        </td>
                        <td class="amount"><strong>{{ $results['total_amount'][$period][$frequency] }}</strong>
                        </td>
                    </tr>
                    </tbody>

                </table>
            @endforeach
        @endforeach
        <table class="alternate">
            <tbody>
            <tr>
                <td colspan="6"></td>
            </tr>
            <tr>
                <td style="width: 20%;"></td>
                <td style="width: 20%;"></td>
                <td style="width: 16%;"></td>
                <td style="width: 20%;" class="amount"><strong>{{ trans('fi.report_total') }}</strong></td>
                <td style="width: 14%;" class="amount">
                    <strong>{{ trans('fi.invoices') }} {{ $results['grand_total_invoice'] }}</strong>
                </td>
                <td style="width: 10%;" class="amount"><strong>{{ $results['grand_total_amount'] }}</strong></td>
            </tr>
            </tbody>
        </table>
    @else
        <h4>{{ trans('fi.no_data_available') }}</h4>
    @endif

@stop