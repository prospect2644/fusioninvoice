@extends('layouts.master')

@section('content')

    <script type="text/javascript">
        $(function () {
            alertify.defaults.theme.ok = "ui negative button";
            alertify.defaults.theme.cancel = "ui black button";

            $("<style>").text(".ajs-header{ background-color: #ba0606 !important; }").appendTo($("body"));

            $('.delete-item-categories').click(function () {

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
            {{ trans('fi.item_categories') }}
        </h1>

        <div class="pull-right">
            <a href="{{ route('item.categories.create') }}" class="btn btn-primary"><i class="fa fa-plus"></i> {{ trans('fi.new') }}</a>
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
                                <th class="text-right">{{ trans('fi.options') }}</th>
                            </tr>
                            </thead>

                            <tbody>
                            @foreach ($itemCategories as $itemCategory)
                                <tr>
                                    <td><a href="{{ route('item.categories.edit', [$itemCategory->id]) }}">{{ $itemCategory->name }}</a></td>
                                    <td align="right">
                                        <a href="#" data-action="{{ route('item.categories.delete',[$itemCategory->id]) }}"
                                           class="btn btn-xs btn-danger delete-item-categories" title=" {{ trans('fi.delete') }}"><i class="fa fa-trash-o"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>

                        </table>
                    </div>

                </div>

                <div class="pull-right">
                    {!! $itemCategories->appends(request()->except('page'))->render() !!}
                </div>

            </div>

        </div>

    </section>

@stop