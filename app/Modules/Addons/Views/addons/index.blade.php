@extends('layouts.master')

@section('content')

    <script type="text/javascript">
        $(function () {
            alertify.defaults.theme.ok = "ui negative button";
            alertify.defaults.theme.cancel = "ui black button";

            $("<style>").text(".ajs-header{ background-color: #ba0606 !important; }").appendTo($("body"));

            $('.disable-addons').click(function () {

                var $_this = $(this);

                alertify.confirm("{!! trans('fi.uninstall_addon_warning') !!}", function () {
                    window.location = decodeURIComponent($_this.data('action'));
                }, function () {
                    alertify.alert().destroy();
                }).setHeader(confirmHeader).set({transition: 'zoom', defaultFocus: 'cancel'});

            });
        });
    </script>

    <section class="content-header">
        <h1>{{ trans('fi.addons') }}</h1>
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
                                <th>{{ trans('fi.name') }}</th>
                                <th>{{ trans('fi.author') }}</th>
                                <th>{{ trans('fi.web_address') }}</th>
                                <th>{{ trans('fi.status') }}</th>
                                <th>{{ trans('fi.options') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($addons as $addon)
                                <tr>
                                    <td>{{ $addon->name }}</td>
                                    <td>{{ $addon->author_name }}</td>
                                    <td>{{ $addon->author_url }}</td>
                                    <td>
                                        @if ($addon->enabled)
                                            <span class="label label-success">{{ trans('fi.enabled') }}</span>
                                        @else
                                            <span class="label label-danger">{{ trans('fi.disabled') }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($addon->enabled)
                                            <a href="#" data-action="{{ route('addons.uninstall', [$addon->id]) }}"
                                               class="btn btn-sm btn-default disable-addons">{{ trans('fi.disable') }}</a>
                                            @if ($addon->has_pending_migrations)
                                                <a href="{{ route('addons.upgrade', [$addon->id]) }}" class="btn btn-sm btn-info">{{ trans('fi.complete_upgrade') }}</a>
                                            @endif
                                        @else
                                            <a href="{{ route('addons.install', [$addon->id]) }}" class="btn btn-sm btn-default">{{ trans('fi.install') }}</a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>

                </div>

            </div>

        </div>

    </section>

@stop