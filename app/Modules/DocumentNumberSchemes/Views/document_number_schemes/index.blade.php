@extends('layouts.master')

@section('content')
    <script type="text/javascript">
        $(function () {
            alertify.defaults.theme.ok = "ui negative button";
            alertify.defaults.theme.cancel = "ui black button";

            $("<style>").text(".ajs-header{ background-color: #ba0606 !important; }").appendTo($("body"));

            $('.delete-document-numbers-scheme').click(function () {

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
            {{ trans('fi.document_number_schemes') }}
        </h1>
        <div class="pull-right">
            <a href="{{ route('documentNumberSchemes.create') }}" class="btn btn-primary"><i class="fa fa-plus"></i> {{ trans('fi.new') }}</a>
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
                                <th>{!! Sortable::link('type', trans('fi.type')) !!}</th>
                                <th>{!! Sortable::link('format', trans('fi.format')) !!}</th>
                                <th>{!! Sortable::link('next_id', trans('fi.next_number')) !!}</th>
                                <th>{!! Sortable::link('left_pad', trans('fi.left_pad')) !!}</th>
                                <th>{!! Sortable::link('reset_number', trans('fi.reset_number')) !!}</th>
                                <th>{{ trans('fi.options') }}</th>
                            </tr>
                            </thead>

                            <tbody>
                            @foreach ($documentNumberSchemes as $documentNumberScheme)
                                <tr>
                                    <td>{{ $documentNumberScheme->name }}</td>
                                    <td>{{ $documentNumberScheme->type }}</td>
                                    <td>{{ $documentNumberScheme->format }}</td>
                                    <td>{{ $documentNumberScheme->next_id }}</td>
                                    <td>{{ $documentNumberScheme->left_pad }}</td>
                                    <td>{{ $resetNumberOptions[$documentNumberScheme->reset_number] }}</td>
                                    <td>
                                        <a href="{{ route('documentNumberSchemes.edit', [$documentNumberScheme->id]) }}" class="btn btn-xs btn-primary" title=" {{ trans('fi.edit') }}">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                        <a href="#" data-action="{{ route('documentNumberSchemes.delete', [$documentNumberScheme->id])}}" class="btn btn-xs btn-danger delete-document-numbers-scheme" title=" {{ trans('fi.delete') }}">
                                            <i class="fa fa-trash-o"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>

                        </table>
                    </div>

                </div>

                <div class="pull-right">
                    {!! $documentNumberSchemes->appends(request()->except('page'))->render() !!}
                </div>

            </div>

        </div>

    </section>

@stop