@extends('reports.layouts.master')
@section('title')
    {{ config('fi.headerTitleText') }} | {{ trans('fi.client_invoice') }}
@stop
@section('content')
    <h1 style="margin-bottom: 0;">{{ trans('fi.client_invoice') }}</h1>
    @foreach ($results as $key => $result)
        @if(count($result['records']) > 0)
            <h2 style="margin-top: 0; margin-bottom: 0;">{{ $result['client_name'] }}</h2>
            <h2 style="margin-top: 0;">{{ $result['from_date'] }} - {{ $result['to_date'] }}</h2>
            <br>
            <table class="alternate">
                <thead>
                <tr>
                    <th>{{ trans('fi.date') }}</th>
                    <th>{{ trans('fi.invoice') }}</th>
                    <th class="amount">{{ trans('fi.total') }}</th>
                    <th class="amount">{{ trans('fi.paid') }}</th>
                    <th class="amount">{{ trans('fi.balance') }}</th>
                </tr>
                </thead>
                <tbody>

                @foreach ($result['records'] as $key => $records)
                    @if(count($result['records']) > 1)
                        <tr>
                            <td colspan="5" align="center"><h2>{{ trans('fi.currency') }}: {{ $key }}</h2></td>
                        </tr>
                    @endif
                    @foreach ($records as $record)
                        <tr>
                            <td>{{ $record['formatted_invoice_date'] }}</td>
                            <td class="{{ $record['type'] == 'credit_memo' ? 'text-danger' : '' }}" title="{{ $record['type'] == 'credit_memo' ? trans('fi.credit_memo') : '' }}">{{ $record['number'] }}</td>
                            <td class="amount">{{ $record['formatted_total'] }}</td>
                            <td class="amount">{{ $record['formatted_paid'] }}</td>
                            <td class="amount">{{ $record['formatted_balance'] }}</td>
                        </tr>
                    @endforeach
                    <tr>
                        <td colspan="2"></td>
                        <td class="amount" style="font-weight: bold;">{{ $result['total'][$key] }}</td>
                        <td class="amount" style="font-weight: bold;">{{ $result['paid'][$key] }}</td>
                        <td class="amount" style="font-weight: bold;">{{ $result['balance'][$key] }}</td>
                    </tr>
                @endforeach

                </tbody>
            </table>
        @endif
    @endforeach
@stop