@extends('reports.layouts.master')
@section('title')
    {{ config('fi.headerTitleText') }} | {{ trans('fi.payments_collected') }}
@stop
@section('content')

    <h1 style="margin-bottom: 0;">{{ trans('fi.payments_collected') }}</h1>
    <h3 style="margin-top: 0;">{{ $results['from_date'] }} - {{ $results['to_date'] }}</h3>
    <h3 style="margin-top: 0;">{{ 'Currency Format: ' . trans($results['currency_format']) }} </h3>

    @foreach ($results['records'] as $paymentMethod => $payments)
        <h2>{{ $paymentMethod }}</h2>

        <table class="alternate">
            <thead>
            <tr>
                <th width="10%">{{ trans('fi.date') }}</th>
                <th width="10%">{{ trans('fi.invoice') }}</th>
                <th width="15%">{{ trans('fi.client') }}</th>
                <th width="10%">{{ trans('fi.payment_method') }}</th>
                <th width="27%">{{ trans('fi.note') }}</th>
                <th width="8%" class="amount">{{ trans('fi.amount') }}</th>
            </tr>
            </thead>

            <tbody>
            @foreach ($payments['payments'] as $payment)
                <tr>
                    <td>{{ $payment['date'] }}</td>
                    <td>{{ $payment['invoice_number'] }}</td>
                    <td>{{ $payment['client_name'] }}</td>
                    <td>{{ $payment['payment_method'] }}</td>
                    <td>{{ $payment['note'] }}</td>
                    
                    @if ($results['currency_format'] == 'fi.base_currency')
                    <td class="amount">{{ $payment['amount'] }}</td>
                    @else
                    <td class="amount">{{ $payment['amount_with_currency'] }}</td> 
                    @endif 

                </tr>
            @endforeach

            <tr>
                <td colspan="5" class="amount"><strong>{{trans('fi.total').": " . $paymentMethod }}</strong></td>
                <td class="amount"><strong>{{ $payments['totals']['amount'] }}</strong></td>
            </tr>

            </tbody>
        </table>

    @endforeach

    <hr>

    <table>
        <tr>
            <td width="20%"></td>
            <td width="20%"></td>
            <td width="20%"></td>
            <td width="20%"></td>
            <td width="10%" class="amount"><strong>{{trans('fi.grand_total')}}</strong></td>
            <td width="10%" class="amount"><strong>{{ $results['grandTotal'] }}</strong></td>
        </tr>
        </tbody>
    </table>

@stop