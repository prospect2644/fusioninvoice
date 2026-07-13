@extends('client_center.layouts.public')

@section('javascript')
    @include('layouts._alertifyjs')
    <script type="text/javascript">
        $(function () {
            $('#view-notes').hide();
            $('.btn-notes').click(function () {
                $('#view-doc').toggle();
                $('#view-notes').toggle();
                $('#' + $(this).data('button-toggle')).show();
                $(this).hide();
            });

            alertify.defaults.theme.ok = "ui negative button";
            alertify.defaults.theme.cancel = "ui black button";

            $("<style>").text(".ajs-header{ background-color: #ba0606 !important; }").appendTo($("body"));

            $('.quote-approve').click(function () {

                var $_this = $(this);

                alertify.confirm("{!! trans('fi.confirm_approve_quote') !!}", function () {
                    window.location = decodeURIComponent($_this.data('action'));
                }, function () {
                    alertify.alert().destroy();
                }).setHeader(confirmHeader).set({transition: 'zoom', defaultFocus: 'cancel'});

            });

            $('.quote-reject').click(function () {

                var $_this = $(this);

                alertify.confirm("{!! trans('fi.confirm_reject_quote') !!}", function () {
                    window.location = decodeURIComponent($_this.data('action'));
                }, function () {
                    alertify.alert().destroy();
                }).setHeader(confirmHeader).set({transition: 'zoom', defaultFocus: 'cancel'});

            });
        });
    </script>
@stop

@section('content')

    <section class="content iframe-content">

        <div class="public-wrapper">

            @include('layouts._alerts')

            <div style="margin-bottom: 15px;">
                <a href="{{ route('clientCenter.public.quote.pdf', [$quote->url_key]) }}" target="_blank"
                   class="btn btn-primary"><i class="fa fa-print"></i> <span>{{ trans('fi.pdf') }}</span>
                </a>
                @if (auth()->check())
                    <a href="javascript:void(0)" id="btn-notes" data-button-toggle="btn-notes-back" class="btn btn-primary btn-notes">
                        <i class="fa fa-comments"></i> {{ trans('fi.notes') }}
                    </a>
                    <a href="javascript:void(0)" id="btn-notes-back" data-button-toggle="btn-notes" class="btn btn-primary btn-notes" style="display: none;">
                        <i class="fa fa-backward"></i> {{ trans('fi.back_to_quote') }}
                    </a>
                @endif
                @if (count($attachments))
                    <div class="btn-group">
                        <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown">
                            <i class="fa fa-files-o"></i> {{ trans('fi.attachments') }} <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu">
                            @foreach ($attachments as $attachment)
                                <li><a href="{{ $attachment->download_url }}">{{ $attachment->filename }}</a></li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                @if (in_array($quote->status_text, ['draft', 'sent']))
                    <a href="#" data-action="{{ route('clientCenter.public.quote.approve', [$quote->url_key]) }}"
                       class="btn btn-primary quote-approve">
                        <i class="fa fa-thumbs-up"></i> {{ trans('fi.approve') }}
                    </a>
                    <a href="#" data-action="{{ route('clientCenter.public.quote.reject', [$quote->url_key]) }}"
                       class="btn btn-primary quote-reject">
                        <i class="fa fa-thumbs-down"></i> {{ trans('fi.reject') }}
                    </a>
                @endif
            </div>

            <div class="public-doc-wrapper resp-container">

                <div id="view-doc">
                    <iframe class="resp-iframe" src="{{ route('clientCenter.public.quote.html', [$urlKey]) }}" frameborder="0"
                        onload="resizeIframeSection(this, 800);"></iframe>
                </div>

                @if (auth()->check())
                    <div id="view-notes">
                        <div class="col-sm-12 table-responsive" style="overflow-x: visible;">
                            <div class="box box-primary">
                                <div class="box-body">
                                    <div class="row">
                                        <div class="col-lg-12">
                                            @include('notes._js_timeline', ['object' => $quote, 'model' => 'FI\Modules\Quotes\Models\Quote', 'hideHeader' => true, 'showPrivateCheckbox' => 1])
                                            <div id="note-timeline-container"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

            </div>

        </div>

    </section>

@stop