@extends('reports.layouts.master')
@section('title')
    {{ config('fi.headerTitleText') }} | {{ trans('fi.item_sales') }}
@stop
@section('content')

    <h1 style="margin-bottom: 0;">{{ trans('fi.item_sales') }}</h1>
    <h3 style="margin-top: 0;">{{ $results['from_date'] }} - {{ $results['to_date'] }}</h3>


    <table class="alternate">
        @if(count($results['records']) > 0)
            @foreach ($results['records'] as $key=>$items)
                <thead>
                <tr>
                    <td colspan="9" align="center"><h2>{{ $key }}</h2></td>
                </tr>
                <tr>
                    <th style="width: 10%; text-align: left;">{{ trans('fi.date') }}</th>
                    <th style="width: 10%; text-align: left;">{{ trans('fi.invoice') }}</th>
                    <th style="width: 15%; text-align: left;">{{ trans('fi.client') }}</th>
                    <th class="amount" style="width: 10%;">{{ trans('fi.price') }}</th>
                    <th class="amount" style="width: 10%;">{{ trans('fi.quantity') }}</th>
                    <th class="amount" style="width: 10%;">{{ trans('fi.subtotal') }}</th>
                    <th class="amount" style="width: 10%;">{{ trans('fi.discount') }}</th>
                    <th class="amount" style="width: 10%;">{{ trans('fi.tax') }}</th>
                    <th class="amount" style="width: 10%;">{{ trans('fi.total') }}</th>
                </tr>
                </thead>

                <tbody>
                @foreach ($items['items'] as $item)
                    <tr>
                        <td>{{ $item['date'] }}</td>
                        <td>{{ $item['invoice_number'] }}</td>
                        <td>{{ $item['client_name'] }}</td>
                        <td class="amount">{{ $item['price'] }}</td>
                        <td class="amount">{{ $item['quantity'] }}</td>
                        <td class="amount">{{ $item['subtotal'] }}</td>
                        <td class="amount">{{ $item['discount'] }}</td>
                        <td class="amount">{{ $item['tax'] }}</td>
                        <td class="amount">{{ $item['total'] }}</td>
                    </tr>
                @endforeach
                <tr>
                    <td colspan="4" class="amount"><strong>{{ trans('fi.total') }}</strong></td>
                    <td class="amount"><strong>{{ $items['totals']['quantity'] }}</strong></td>
                    <td class="amount"><strong>{{ $items['totals']['subtotal'] }}</strong></td>
                    <td class="amount"><strong>{{ $items['totals']['discount'] }}</strong></td>
                    <td class="amount"><strong>{{ $items['totals']['tax'] }}</strong></td>
                    <td class="amount"><strong>{{ $items['totals']['total'] }}</strong></td>
                </tr>
                </tbody>
            @endforeach
            <tr>
                <td colspan="8" class="amount"><strong>{{ trans('fi.total') }}</strong></td>
                <td class="amount"><strong>{{ $results['grand_total'] }}</strong></td>
            </tr>
        @else
            <tr><td><h4>{{ trans('fi.no_records_found') }}</h4></td></tr>
        @endif
    </table>


@stop