<link rel="stylesheet" href="{{ asset('assets/plugins/chosen/chosen.min.css') }}">
<script src="{{ asset('assets/plugins/chosen/chosen.jquery.min.js') }}" type="text/javascript"></script>

@include('settings._js_mail')

<div class="modal fade" id="modal-mail-test">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">{{ trans('fi.send_test_email') }}</h4>
            </div>
            <div class="modal-body">

                <div id="modal-status-placeholder"></div>

                <form class="form-horizontal">

                    <div class="form-group">
                        <label class="col-sm-3 control-label">{{ trans('fi.from') }}</label>

                        <div class="col-sm-9">
                            {!! Form::select('from', $fromMail,'', ['id' => 'from', 'class' => 'form-control input-sm']) !!}
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label">{{ trans('fi.to') }}</label>

                        <div class="col-sm-9">
                            {!! Form::select('to', $to, config('fi.testEmailAddress') != '' ? config('fi.testEmailAddress') : null, ['id' => 'to', 'class' => 'form-control', 'multiple' => true]) !!}
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label">{{ trans('fi.cc') }}</label>

                        <div class="col-sm-9">
                            {!! Form::select('cc', $cc, config('fi.mailDefaultCc') != '' ? config('fi.mailDefaultCc') : null, ['id' => 'cc', 'class' => 'form-control', 'multiple' => true]) !!}
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label">{{ trans('fi.bcc') }}</label>

                        <div class="col-sm-9">
                            {!! Form::select('bcc', $bcc, config('fi.mailDefaultBcc') != '' ? config('fi.mailDefaultBcc') : null, ['id' => 'bcc', 'class' => 'form-control', 'multiple' => true]) !!}
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label">{{ trans('fi.subject') }}</label>

                        <div class="col-sm-9">
                            {!! Form::text('subject', $subject, ['id' => 'subject', 'class' => 'form-control input-sm']) !!}
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label">{{ trans('fi.body') }}</label>

                        <div class="col-sm-9">
                            {!! Form::textarea('body', $body, ['id' => 'body', 'class' => 'form-control input-sm', 'rows' => 3]) !!}
                        </div>
                    </div>
                </form>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('fi.cancel') }}</button>
                <button type="button" id="btn-submit-mail-test" class="btn btn-primary" data-loading-text="{{ trans('fi.sending') }}...">{{ trans('fi.send') }}</button>
            </div>
        </div>
    </div>
</div>