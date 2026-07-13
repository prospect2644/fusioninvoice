@extends('layouts.master')

@section('content')

    <script type="text/javascript">
        $(function () {
            alertify.defaults.theme.ok = "ui negative button";
            alertify.defaults.theme.cancel = "ui black button";

            $("<style>").text(".ajs-header{ background-color: #ba0606 !important; }").appendTo($("body"));

            $('.delete-tax-rate').click(function () {

                var $_this = $(this);

                alertify.confirm("{!! trans('fi.delete_record_warning') !!}", function () {
                    window.location = decodeURIComponent($_this.data('action'));
                }, function () {
                    alertify.alert().destroy();
                }).setHeader(confirmHeader).set({transition: 'zoom', defaultFocus: 'cancel'});

            });
        });
    </script>

    <section class="content-header">
        <h1 class="pull-left">
            {{ trans('fi.tax_rates') }}
        </h1>

        <div class="pull-right">
            <a href="{{ route('taxRates.create') }}" class="btn btn-primary"><i class="fa fa-plus"></i> {{ trans('fi.new') }}</a>
        </div>
        <div class="clearfix"></div>
    </section>

    <section class="content">

        @include('layouts._alerts')

        <div class="row">

            <div class="col-xs-12">

                <div class="box box-primary">

                    <div class="box-body no-padding">
                        <table class="table table-hover table-striped">

                            <thead>
                            <tr>
                                <th>{!! Sortable::link('name', trans('fi.name')) !!}</th>
                                <th>{!! Sortable::link('percent', trans('fi.percent')) !!}</th>
                                <th>{!! Sortable::link('is_compound', trans('fi.compound')) !!}</th>
                                <th class="text-right">{{ trans('fi.options') }}</th>
                            </tr>
                            </thead>

                            <tbody>
                            @foreach ($taxRates as $taxRate)
                                <tr>
                                    <td>{{ $taxRate->name }}</td>
                                    <td>{{ $taxRate->formatted_percent }}</td>
                                    <td>{{ $taxRate->formatted_is_compound }}</td>
                                    <td align="right">
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown">
                                                {{ trans('fi.options') }} <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-right">
                                                <li><a href="{{ route('taxRates.edit', [$taxRate->id]) }}"><i class="fa fa-edit"></i> {{ trans('fi.edit') }}</a></li>
                                                <li><a href="#"
                                                       data-action="{{ route('taxRates.delete',[$taxRate->id]) }}"
                                                       class="delete-tax-rate text-danger"><i
                                                                class="fa fa-trash-o"></i> {{ trans('fi.delete') }}</a>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>

                        </table>
                    </div>

                </div>

                <div class="pull-right">
                    {!! $taxRates->appends(request()->except('page'))->render() !!}
                </div>

            </div>

        </div>

    </section>

@stop