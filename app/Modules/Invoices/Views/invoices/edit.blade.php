@extends('layouts.master')

@section('javascript')

    @include('layouts._datepicker')
    @include('layouts._select2')
    @include('item_lookups._js_item_lookups')
    @include('layouts._formdata')
    @include('layouts._typeahead')

@stop

@section('content')

    <div id="div-invoice-edit">

        @include('invoices._edit')

    </div>

@stop