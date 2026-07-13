@include('layouts._datepicker')
@include('layouts._select2')
@include('clients._js_lookup')
@include('invoices._js_create')

<div class="modal fade" id="create-invoice">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title" id="invoice_create_title">{{ trans('fi.create_invoice') }}</h4>
            </div>
            <div class="modal-body">

                <div id="modal-status-placeholder"></div>

                <form class="form-horizontal">

                    <input type="hidden" name="user_id" value="{{ auth()->user()->id }}" id="user_id">

                    <div class="form-group">
                        <div class="pull-right" style="margin-right: 17px;">
                            <label class="radio-inline text-bold col-lg-pull-0">{!! Form::radio('type', 'invoice', true, ['class' => 'add-line-item']) !!} {{ trans('fi.invoice') }}</label>
                            <label class="radio-inline text-bold col-lg-pull-0">{!! Form::radio('type', 'credit_memo', false, ['class' => 'add-line-item']) !!} {{ trans('fi.credit_memo') }}</label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label">{{ trans('fi.client') }}</label>

                        <div class="col-sm-9">
                            {!! Form::select('client_name', $clients, null, ['id' => 'create_client_name', 'class' => 'form-control client-lookup', 'autocomplete' => 'off', 'style'=>"width: 100%;"]) !!}
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label">{{ trans('fi.date') }}</label>

                        <div class="col-sm-9">
                            {!! Form::text('invoice_date', date(config('fi.dateFormat')), ['id' =>
                            'create_invoice_date', 'class' => 'form-control', 'autocomplete' => 'off']) !!}
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label">{{ trans('fi.company_profile') }}</label>

                        <div class="col-sm-9">
                            {!! Form::select('company_profile_id', $companyProfiles, config('fi.defaultCompanyProfile'),
                            ['id' => 'company_profile_id', 'class' => 'form-control']) !!}
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label">{{ trans('fi.document_number_scheme') }}</label>

                        <div class="col-sm-9">
                            {!! Form::select('document_number_scheme_id', $documentNumberSchemes['invoice'], config('fi.invoiceGroup'),
                            ['id' => 'create_document_number_scheme_id', 'class' => 'form-control']) !!}
                        </div>
                    </div>

                </form>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('fi.cancel') }}</button>
                <button type="button" id="invoice-create-confirm" class="btn btn-primary">{{ trans('fi.submit') }}
                </button>
            </div>
        </div>
    </div>
</div>