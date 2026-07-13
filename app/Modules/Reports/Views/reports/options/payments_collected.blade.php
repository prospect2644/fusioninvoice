@extends('layouts.master')

@section('javascript')

    @include('layouts._daterangepicker')

    <script type="text/javascript">
        $(function () {
            $('#btn-run-report').click(function (e) {
                e.preventDefault();
                var from_date = $('#from_date').val();
                var to_date = $('#to_date').val();
                var company_profile_id = $('#company_profile_id').val();
                var prepayments = $('#prepayments').val();
                var currency_format = $('#currency_format').val();

                $.post("{{ route('reports.paymentsCollected.validate') }}", {
                    from_date: from_date,
                    to_date: to_date,
                    company_profile_id: company_profile_id,
                    prepayments: prepayments,
                    currency_format: currency_format,
                }).done(function () {
                    clearErrors();
                    $('#form-validation-placeholder').html('');
                    output_type = $("input[name=output_type]:checked").val();
                    query_string = "?from_date=" + from_date + "&to_date=" + to_date + "&company_profile_id=" + company_profile_id+ "&prepayments=" + prepayments + "&currency_format=" + currency_format;
                    if (output_type == 'preview') {
                        $('#preview').show();
                        $('#preview-results').attr('src', "{{ route('reports.paymentsCollected.html') }}" + query_string);
                    }
                    else if (output_type == 'pdf') {
                        window.open("{{ route('reports.paymentsCollected.pdf') }}" + query_string, '_blank');
                    }

                }).fail(function (response) {
                    showAlertifyErrors($.parseJSON(response.responseText).errors);
                });
            });
        });
    </script>

@stop

@section('content')

    <section class="content-header">
        <h1 class="fa fa-bar-chart-o pull-left"> </h1>
        <h1 class="pull-left">{{ trans('fi.payments_collected') }}</h1>

        <div class="pull-right">
            <button class="btn btn-primary" id="btn-run-report">{{ trans('fi.run_report') }}</button>
        </div>
        <div class="clearfix"></div>
    </section>

    <section class="content">

        <div id="form-validation-placeholder"></div>

        <div class="row">

            <div class="col-md-12">

                <div class="box box-primary">
                    <div class="box-header">
                        <h3 class="box-title">{{ trans('fi.options') }}</h3>
                    </div>
                    <div class="box-body">

                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>{{ trans('fi.company_profile') }}:</label>
                                    {!! Form::select('company_profile_id', $companyProfiles, null, ['id' => 'company_profile_id', 'class' => 'form-control'])  !!}
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>{{ trans('fi.date_range') }}:</label>
                                    {!! Form::hidden('from_date', null, ['id' => 'from_date']) !!}
                                    {!! Form::hidden('to_date', null, ['id' => 'to_date']) !!}
                                    {!! Form::text('date_range', null, ['id' => 'date_range', 'class' => 'form-control', 'readonly' => 'readonly']) !!}
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>{{ trans('fi.include_prepayments') }}:</label>
                                    {!! Form::select('prepayments', ['include_prepayments' => trans('fi.include_prepayments'), 'include_prepayments_applied' => trans('fi.include_prepayments_applied')], null, ['id' => 'prepayments', 'class' => 'form-control'])  !!}
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>{{ trans('fi.currency_format') }}:</label>
                                    {!! Form::select('currency_format', ['fi.base_currency' => trans('fi.base_currency'), 'fi.invoice_currency' => trans('fi.invoice_currency')], null, ['id' => 'currency_format', 'class' => 'form-control'])  !!}
                                </div>
                            </div>

                        </div>

                        <div class="row">
                            <div class="col-md-3">
                                <div class="input-group">
                                    <label>{{ trans('fi.output_type') }}:</label><br>
                                    <label class="radio-inline">
                                        <input type="radio" name="output_type" value="preview"
                                               checked="checked"> {{ trans('fi.preview') }}
                                    </label>
                                    <label class="radio-inline">
                                        <input type="radio" name="output_type" value="pdf"> {{ trans('fi.pdf') }}
                                    </label>
                                </div>
                            </div>
                        </div>

                    </div>

                </div>
            </div>

        </div>

        <div class="row" id="preview" style="display: none;">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-body">
                        <iframe src="about:blank" id="preview-results" frameborder="0" style="width: 100%;" scrolling="no"
                                                onload="resizeIframe(this, 500);"></iframe>
                    </div>
                </div>
            </div>
        </div>

    </section>

@stop