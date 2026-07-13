@include('layouts._datepicker')
@include('layouts._select2')
@include('clients._js_lookup')
@include('invoices._js_copy')

<div class="modal fade" id="modal-copy-invoice">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">{{ trans('fi.copy') }}</h4>
            </div>
            <div class="modal-body">

                <div id="modal-status-placeholder"></div>

                <form class="form-horizontal">

                    <div class="form-group">
                        <label class="col-sm-3 control-label">{{ trans('fi.client') }}</label>

                        <div class="col-sm-9">
                            {!! Form::select('client_name', $clients, $invoice->client->name, ['id' => 'copy_client_name', 'class' => 'form-control client-lookup', 'autocomplete' => 'off', 'style'=>"width: 100%;"]) !!}
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label">{{ trans('fi.date') }}</label>

                        <div class="col-sm-9">
                            {!! Form::text('invoice_date', date(config('fi.dateFormat')), ['id' => 'copy_invoice_date', 'class' => 'form-control']) !!}
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label">{{ trans('fi.company_profile') }}</label>

                        <div class="col-sm-9">
                            {!! Form::select('company_profile_id', $companyProfiles, config('fi.defaultCompanyProfile'),
                            ['id' => 'copy_company_profile_id', 'class' => 'form-control']) !!}
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label">{{ trans('fi.document_number_scheme') }}</label>

                        <div class="col-sm-9">
                            {!! Form::select('document_number_scheme_id', $documentNumberSchemes, $invoice->document_number_scheme_id, ['id' => 'copy_document_number_scheme_id', 'class' => 'form-control']) !!}
                        </div>
                    </div>

                </form>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('fi.cancel') }}</button>
                <button type="button" id="btn-copy-invoice-submit"
                        class="btn btn-primary">{{ trans('fi.submit') }}</button>
            </div>
        </div>
    </div>
</div>