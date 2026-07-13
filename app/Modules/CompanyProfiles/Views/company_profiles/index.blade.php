@extends('layouts.master')

@section('content')

    <script type="text/javascript">
        $(function () {
            alertify.defaults.theme.ok = "ui negative button";
            alertify.defaults.theme.cancel = "ui black button";

            $("<style>").text(".ajs-header{ background-color: #ba0606 !important; }").appendTo($("body"));

            $('.delete-company-profile').click(function () {

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
            {{ trans('fi.company_profiles') }}
        </h1>

        <div class="pull-right">
            <a href="{{ route('companyProfiles.create') }}" class="btn btn-primary"><i class="fa fa-plus"></i> {{ trans('fi.new') }}</a>
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
                                <th>{{ trans('fi.company') }}</th>
                                <th class="text-right">{{ trans('fi.options') }}</th>
                            </tr>
                            </thead>

                            <tbody>
                            @foreach ($companyProfiles as $companyProfile)
                                <tr>
                                    <td><a href="{{ route('companyProfiles.edit', [$companyProfile->id]) }}">{{ $companyProfile->company }}</a></td>
                                    <td align="right">
                                        <a href="javascript:void(0);" class="btn btn-xs btn-danger delete-company-profile" title="{{ trans('fi.delete') }}"
                                           data-action="{{ route('companyProfiles.delete',[$companyProfile->id]) }}">
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
                    {!! $companyProfiles->appends(request()->except('page'))->render() !!}
                </div>

            </div>

        </div>

    </section>

@stop