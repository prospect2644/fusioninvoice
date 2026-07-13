@extends('client_center.layouts.public')

@section('javascript')

    <script type="text/javascript">
        $(function () {
            $('#view-notes').hide();
            $('.btn-notes').click(function () {
                $('#view-doc').toggle();
                $('#view-notes').toggle();
                $('#' + $(this).data('button-toggle')).show();
                $(this).hide();
            });

            $('.btn-pay').click(function () {
                $(this).attr("disabled", true);
            });
        });
    </script>
@stop

@section('content')

    <section class="content iframe-content">

        <div class="public-wrapper">

            @include('layouts._alerts')

            <div style="margin-bottom: 15px;">

                <a href="{{ route('clientCenter.public.invoice.pdf', [$invoice->url_key]) }}" target="_blank"
                   class="btn btn-primary"><i class="fa fa-print"></i> <span>{{ trans('fi.pdf') }}</span>
                </a>

                @if (auth()->check())
                    <a href="javascript:void(0)" id="btn-notes" data-button-toggle="btn-notes-back" class="btn btn-primary btn-notes">
                        <i class="fa fa-comments"></i> {{ trans('fi.notes') }}
                    </a>
                    <a href="javascript:void(0)" id="btn-notes-back" data-button-toggle="btn-notes" class="btn btn-primary btn-notes" style="display: none;">
                        <i class="fa fa-backward"></i> {{ ($invoice->type == 'credit_memo') ? trans('fi.back_to_credit_memo') :  trans('fi.back_to_invoice') }}
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

                @if ($invoice->isPayable)
                    @foreach ($merchantDrivers as $driver)
                        <a href="{{ route('merchant.pay.' . strtolower($driver->getName()), [$invoice->url_key]) }}" class="btn btn-success btn-pay" ><i class="fa fa-credit-card"></i> {{ $driver->getSetting('paymentButtonText') }}</a>
                    @endforeach
                @endif
            </div>

            <div class="public-doc-wrapper">

                <div id="view-doc">
                    <iframe class="resp-iframe" src="{{ route('clientCenter.public.invoice.html', [$urlKey]) }}" onload="resizeIframeSection(this, 800);"></iframe>
                </div>

                @if (auth()->check())
                    <div id="view-notes">
                        <div class="col-sm-12 table-responsive" style="overflow-x: visible;">
                            <div class="box box-primary">
                                <div class="box-body">
                                    <div class="row">
                                        <div class="col-lg-12">
                                            @include('notes._js_timeline', ['object' => $invoice, 'model' => 'FI\Modules\Invoices\Models\Invoice', 'hideHeader' => true, 'showPrivateCheckbox' => 1])
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