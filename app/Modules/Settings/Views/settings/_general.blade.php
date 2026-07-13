@section('javascript')
    @parent
    <script type="text/javascript">
        $().ready(function () {
            $('.btn-check-update').click(function () {
                $.ajax({
                    url: '{{ route('settings.updateCheck') }}',
                    method: 'get',
                    beforeSend: function () {
                        $(".modal-loader").show();
                    },
                    success: function (response) {
                        $(".modal-loader").hide();
                        alertify.success(response.message, 5);
                    },
                    error: function () {
                        $(".modal-loader").hide();
                        alertify.error('{{ trans('fi.unknown_error') }}', 5);
                    }
                });
            });
        });
    </script>
@stop

<div class="row">

    <div class="col-md-3">
        <div class="form-group">
            <label>{{ trans('fi.header_title_text') }}: </label>
            {!! Form::text('setting[headerTitleText]', config('fi.headerTitleText'), ['class' => 'form-control']) !!}
        </div>
    </div>

    <div class="col-md-3">
        <div class="form-group">
            <label>{{ trans('fi.default_company_profile') }}: </label>
            {!! Form::select('setting[defaultCompanyProfile]', $companyProfiles, config('fi.defaultCompanyProfile'), ['class' => 'form-control']) !!}
        </div>
    </div>

    <div class="col-md-3">
        <div class="form-group">
            <label>{{ trans('fi.language') }}: </label>
            {!! Form::select('setting[language]', $languages, config('fi.language'), ['class' => 'form-control']) !!}
        </div>
    </div>

    <div class="col-md-3">
        <div class="form-group">
            <label>{{ trans('fi.version') }}: </label>

            <div class="input-group">
                {!! Form::text('version', config('fi.version'), ['class' => 'form-control', 'disabled' => 'disabled']) !!}
                <span class="input-group-btn">
					<button class="btn btn-info btn-check-update"
                            type="button">{{ trans('fi.check_for_update') }}</button>
				</span>
            </div>
        </div>
    </div>

</div>

<div class="row">

    <div class="col-md-2">
        <div class="form-group">
            <label>{{ trans('fi.skin') }}: </label>
            {!! Form::select('setting[skin]', $skins, config('fi.skin'), ['class' => 'form-control']) !!}
        </div>
    </div>

    <div class="col-md-2">
        <div class="form-group">
            <label>{{ trans('fi.date_format') }}: </label>
            {!! Form::select('setting[dateFormat]', $dateFormats, config('fi.dateFormat'), ['class' => 'form-control']) !!}
        </div>
    </div>

    <div class="col-md-2">
        <div class="form-group">
            <label>{{ trans('fi.use_24_hour_time_format') }}: </label>
            {!! Form::select('setting[use24HourTimeFormat]', $yesNoArray, config('fi.use24HourTimeFormat'), ['class' => 'form-control']) !!}
        </div>
    </div>

    <div class="col-md-2">
        <div class="form-group">
            <label>{{ trans('fi.timezone') }}: </label>
            {!! Form::select('setting[timezone]', $timezones, config('fi.timezone'), ['class' => 'form-control']) !!}
        </div>
    </div>

    <div class="col-md-2">
        <div class="form-group">
            <label>{{ trans('fi.use_captcha_in_login') }}: </label>
            {!! Form::select('setting[useCaptchInLogin]', $yesNoArray, config('fi.useCaptchInLogin'), ['class' => 'form-control']) !!}
        </div>
    </div>
    <div class="col-md-2">
        <div class="form-group">
            <label>{{ trans('fi.results_per_page') }}:</label>
            {!! Form::select('setting[resultsPerPage]', $resultsPerPage, config('fi.resultsPerPage'), ['class' => 'form-control']) !!}
        </div>
    </div>
</div>
<div class="row">

    <div class="col-md-6">

        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label>{{ trans('fi.display_client_unique_name') }}: </label>
                    {!! Form::select('setting[displayClientUniqueName]', $clientUniqueNameOptions, config('fi.displayClientUniqueName'), ['class' => 'form-control']) !!}
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>{{ trans('fi.quantity_price_decimals') }}: </label>
                            {!! Form::select('setting[amountDecimals]', $amountDecimalOptions, config('fi.amountDecimals'), ['class' => 'form-control']) !!}
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>{{ trans('fi.round_tax_decimals') }}: </label>
                            {!! Form::select('setting[roundTaxDecimals]', $roundTaxDecimalOptions, config('fi.roundTaxDecimals'), ['class' => 'form-control']) !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <div class="col-md-6">
        <div class="form-group">
            <label>{{ trans('fi.address_format') }}: </label>
            {!! Form::textarea('setting[addressFormat]', config('fi.addressFormat'), ['class' => 'form-control', 'rows' => 5]) !!}
        </div>
    </div>

</div>

<div class="row">

    <div class="col-md-2">
        <div class="form-group">
            <label>{{ trans('fi.base_currency') }}: </label>
            {!! Form::select('setting[baseCurrency]', $currencies, config('fi.baseCurrency'), ['class' => 'form-control']) !!}
        </div>
    </div>

    <div class="col-md-2">
        <div class="form-group">
            <label>{{ trans('fi.exchange_rate_mode') }}: </label>
            {!! Form::select('setting[exchangeRateMode]', $exchangeRateModes, config('fi.exchangeRateMode'), ['class' => 'form-control']) !!}
        </div>
    </div>
    <div class="col-md-2">
        <label>{{ trans('fi.number_of_tax_fields') }}: </label>
        {!! Form::select('setting[numberOfTaxFields]', $numberOfTaxFieldsArray, config('fi.numberOfTaxFields') ? config('fi.numberOfTaxFields') :  2, ['class' => 'form-control']) !!}
    </div>
    <div class="col-md-4">
        <label>{{ trans('fi.custom_fields_column_width') }}: </label>
        {!! Form::select('setting[customFieldsDisplayColumn]', $customFieldColWidthArray, config('fi.customFieldsDisplayColumn') ? config('fi.customFieldsDisplayColumn') :  12, ['class' => 'form-control']) !!}
    </div>
    <div class="col-md-2">
        <div class="form-group">
            <label>{{ trans('fi.require_tags_on_client_notes') }}: </label>
            {!! Form::select('setting[requireTagsOnClientNotes]', $yesNoArray, config('fi.requireTagsOnClientNotes'), ['class' => 'form-control']) !!}
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <label>{{ trans('fi.force_https') }}:</label>
            {!! Form::select('setting[forceHttps]', $yesNoArray, config('fi.forceHttps'), ['class' => 'form-control']) !!}
            <p class="help-block small">{{ trans('fi.force_https_help') }}</p>
        </div>
    </div>

</div>

<div class="row">
    <div class="col-md-6 mt-10">
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <button type="button" class="btn btn-primary btn-sm" id="btn-generate-passport-key"
                            data-loading-text="{{ trans('fi.generate_passport_key_wait') }}">
                        <i class="fa fa-key"></i> {{ trans('fi.generate_passport_key') }}
                    </button>
                    <button type="button" class="btn btn-danger btn-sm" id="btn-delete-orphan-tags"
                            data-loading-text="{{ trans('fi.deleting_tags_wait') }}">
                        <i class="fa fa-trash"></i> {{ trans('fi.delete_tags') }}
                    </button>
                    <button type="button" class="btn btn-danger btn-sm" id="btn-pdf-cleanup"
                            data-loading-text="{{ trans('fi.deleting_pdf_wait') }}">
                        <i class="fa fa-file-pdf-o"></i> {{ trans('fi.pdf_cleanup') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
