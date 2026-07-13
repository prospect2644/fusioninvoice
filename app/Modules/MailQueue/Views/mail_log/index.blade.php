@extends('layouts.master')

@section('javascript')
    <script type="text/javascript">
        $(function () {
            $('.btn-show-content').click(function () {
                $('#modal-placeholder').load('{{ route('mailLog.content') }}', {
                    id: $(this).data('id')
                });
            });

            alertify.defaults.theme.ok = "ui negative button";
            alertify.defaults.theme.cancel = "ui black button";

            $("<style>").text(".ajs-header{ background-color: #ba0606 !important; }").appendTo($("body"));

            $('.delete-email-log').click(function () {

                var $_this = $(this);

                alertify.confirm("{!! trans('fi.delete_record_warning') !!}", function () {
                    window.location = decodeURIComponent($_this.data('action'));
                }, function () {
                    alertify.alert().destroy();
                }).setHeader(confirmHeader).set({transition: 'zoom', defaultFocus: 'cancel'});

            });
        });
    </script>
@stop

@section('content')

    <section class="content-header">
        <h1>{{ trans('fi.mail_log') }}</h1>
    </section>

    <section class="content">

        @include('layouts._alerts')

        <div class="row">

            <div class="col-xs-12">

                <div class="box box-primary">

                    <div class="box-body no-padding">
                        <table class="table table-hover table-striped small">

                            <thead>
                            <tr>
                                <th>{!! Sortable::link('created_at', trans('fi.date')) !!}</th>
                                <th>{!! Sortable::link('to', trans('fi.to')) !!}</th>
                                <th>{!! Sortable::link('subject', trans('fi.subject')) !!}</th>
                                <th>{!! Sortable::link('from', trans('fi.from')) !!}</th>
                                <th>{!! Sortable::link('cc', trans('fi.cc')) !!}</th>
                                <th>{!! Sortable::link('bcc', trans('fi.bcc')) !!}</th>
                                <th>{!! Sortable::link('sent', trans('fi.sent')) !!}</th>
                                <th>{{ trans('fi.options') }}</th>
                            </tr>
                            </thead>

                            <tbody>
                            @foreach ($mails as $mail)
                                <tr>
                                    <td>{{ $mail->formatted_created_at }}</td>
                                    <td>{{ $mail->formatted_to }}</td>
                                    <td><a href="javascript:void(0)" class="btn-show-content" data-id="{{ $mail->id }}">{{ $mail->subject }}</a></td>
                                    <td>{{ $mail->formatted_from }}</td>
                                    <td>{{ $mail->formatted_cc }}</td>
                                    <td>{{ $mail->formatted_bcc }}</td>
                                    <td align="center">{!! $mail->formatted_sent !!}</td>
                                    <td align="right">
                                        <a href="#" data-action="{{ route('mailLog.delete', [$mail->id])
                                                }}" class="delete-email-log btn btn-xs btn-danger" title="{{ trans('fi.delete') }}">
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
                    {!! $mails->appends(request()->except('page'))->render() !!}
                </div>

            </div>

        </div>

    </section>
@stop