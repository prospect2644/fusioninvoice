@extends('layouts.master')

@section('javascript')

    @include('layouts._datepicker')
    @include('layouts._formdata')
    @include('layouts._select2')
    @include('item_lookups._js_item_lookups')
    @include('layouts._typeahead')

@stop

@section('content')

    <div id="div-recurring-invoice-edit">

        @include('recurring_invoices._edit')

    </div>

@stop