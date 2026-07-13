@section('javascript')
    @parent
    <script type="text/javascript">
        $(function () {

            $('#mailPassword').val('');

            $('#lbl-save-first').hide();

            updateEmailOptions();

            $('#mailDriver').change(function () {
                updateEmailOptions();
            });

            function updateEmailOptions() {

                $('.email-option').hide();

                mailDriver = $('#mailDriver').val();

                if (mailDriver == 'smtp') {
                    $('.smtp-option').show();
                } else if (mailDriver == 'sendmail') {
                    $('.sendmail-option').show();
                } else if (mailDriver == 'mail') {
                    $('.phpmail-option').show();
                } else if (mailDriver == 'sendgrid') {
                    $('.sendgrid-option').show();
                }
            }

            $('#mailPassword').val("{{ $mailPassword }}");

            $('#testEmailAddress, #mailDriver, #mailHost, #mailPort, #mailUsername, #mailPassword, #mailEncryption, #mailAllowSelfSignedCertificate, #mailSendmail').change(function () {
                $('#btn-test-email').attr('disabled', true);
                $('#lbl-save-first').show();
            });

            $('#btn-test-email').click(function () {
                $('#modal-placeholder').load('{{ route('testMail.create') }}', {}, function (response, status, xhr) {
                    if (status == 'error') {
                        alertify.error('{{ trans('fi.problem_with_email_template') }}');
                    }
                });
            });

        });
    </script>
@stop

<div class="form-group">
    <label>{{ trans('fi.email_send_method') }}: </label>
    {!! Form::select('setting[mailDriver]', $emailSendMethods, config('fi.mailDriver'), ['id' => 'mailDriver', 'class' => 'form-control']) !!}
</div>

<div class="row smtp-option email-option">
    <div class="col-md-9">
        <div class="form-group smtp-option email-option">
            <label>{{ trans('fi.smtp_host_address') }}: </label>
            {!! Form::text('setting[mailHost]', config('fi.mailHost'), ['id' => 'mailHost', 'class' => 'form-control']) !!}
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group smtp-option email-option">
            <label>{{ trans('fi.smtp_host_port') }}: </label>
            {!! Form::text('setting[mailPort]', config('fi.mailPort'), ['id' => 'mailPort', 'class' => 'form-control']) !!}
        </div>
    </div>
</div>

<div class="form-group sendgrid-option email-option">
    <div class="form-group">
        <label>{{ trans('fi.sendgrid_key') }}: </label>
        {!! Form::text('setting[mailSendgridKey]', config('fi.mailSendgridKey'), ['id'=>'mailSendgridKey', 'class' => 'form-control']) !!}
    </div>
</div>

<div class="row smtp-option email-option sendgrid-option">
    <div class="col-md-3">
        <div class="form-group smtp-option email-option">
            <label>{{ trans('fi.smtp_username') }}: </label>
            {!! Form::text('setting[mailUsername]', config('fi.mailUsername'), ['id' => 'mailUsername', 'class' => 'form-control']) !!}
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group smtp-option email-option">
            <label>{{ trans('fi.smtp_password') }}: </label>
            {!! Form::password('setting[mailPassword]', ['id' => 'mailPassword', 'class' => 'form-control']) !!}
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group smtp-option email-option">
            <label>{{ trans('fi.smtp_encryption') }}: </label>
            {!! Form::select('setting[mailEncryption]', $emailEncryptions, config('fi.mailEncryption'), ['id' => 'mailEncryption', 'class' => 'form-control']) !!}
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group smtp-option email-option sendgrid-option">
            <label>{{ trans('fi.allow_self_signed_cert') }}: </label>
            {!! Form::select('setting[mailAllowSelfSignedCertificate]', $yesNoArray, config('fi.mailAllowSelfSignedCertificate'), ['id'=>'mailAllowSelfSignedCertificate', 'class' => 'form-control']) !!}
        </div>
    </div>
</div>

<div class="form-group sendmail-option email-option">
    <div class="form-group">
        <label>{{ trans('fi.sendmail_path') }}: </label>
        {!! Form::text('setting[mailSendmail]', config('fi.mailSendmail'), ['id'=>'mailSendmail', 'class' => 'form-control']) !!}
    </div>
</div>

<div class="row smtp-option sendmail-option phpmail-option email-option sendgrid-option">
    <div class="col-md-3">
        <div class="form-group smtp-option sendmail-option phpmail-option email-option sendgrid-option">
            <label>{{ trans('fi.always_attach_pdf') }}: </label>
            {!! Form::select('setting[attachPdf]', $yesNoArray, config('fi.attachPdf'), ['id' => 'attachPdf', 'class' => 'form-control']) !!}
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group smtp-option sendmail-option phpmail-option email-option sendgrid-option">
            <label>{{ trans('fi.reply_to_address') }}: </label>
            {!! Form::text('setting[mailReplyToAddress]', config('fi.mailReplyToAddress'), ['class' => 'form-control', 'autocomplete' => 'off']) !!}
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group smtp-option sendmail-option phpmail-option email-option sendgrid-option">
            <label>{{ trans('fi.always_cc') }}: </label>
            {!! Form::text('setting[mailDefaultCc]', config('fi.mailDefaultCc'), ['class' => 'form-control', 'autocomplete' => 'off']) !!}
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group smtp-option sendmail-option phpmail-option email-option sendgrid-option">
            <label>{{ trans('fi.always_bcc') }}: </label>
            {!! Form::text('setting[mailDefaultBcc]', config('fi.mailDefaultBcc'), ['class' => 'form-control', 'autocomplete' => 'off']) !!}
        </div>
    </div>
</div>


<div class="row">
    <div class="col-md-3">
        <div class="form-group">
            <label>{{ trans('fi.test_email_address') }}: </label>
            {!! Form::text('setting[testEmailAddress]', config('fi.testEmailAddress'), ['id'=>'testEmailAddress', 'class' => 'form-control', 'autocomplete' => 'off']) !!}
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <br>
            <a href="javascript:void(0)" class="btn btn-info btn-sm" id="btn-test-email">
                <i class="fa fa-envelope"></i> {{ trans('fi.send_test_email') }}
            </a>

            <p><span class="small text-aqua" id="lbl-save-first">{{ trans('fi.test-mail-btn-title') }}</span></p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>{{ trans('fi.mail_from_address') }}: </label>
            {!! Form::text('setting[mailFromAddress]', config('fi.mailFromAddress'), ['id'=>'mailFromAddress', 'class' => 'form-control', 'autocomplete' => 'off']) !!}
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>{{ trans('fi.mail_from_name') }}: </label>
            {!! Form::text('setting[mailFromName]', config('fi.mailFromName'), ['id'=>'mailFromName', 'class' => 'form-control', 'autocomplete' => 'off']) !!}
        </div>
    </div>
</div>
<hr>
<div class="row">
    <div class="col-md-6">
        <div class="box box-primary">
            <div class="box-header">
                <h3 class="box-title">{{ trans('fi.quotes') }}</h3>

                <div class="box-tools pull-right">
                   <span class="help-block">
                        <a href="https://www.fusioninvoice.com/docs/2020/Customization/Email-Templates#quote-email-template"
                           target="_blank">{{ trans('fi.available_fields') }}
                        </a>
                    </span>
                </div>
            </div>
            <div class="box-body">
                <div class="form-group">
                    <label>{{ trans('fi.quote_email_subject') }}: </label>
                    {!! Form::text('setting[quoteEmailSubject]', config('fi.quoteEmailSubject'), ['class' => 'form-control']) !!}
                </div>
                <div class="form-group">
                    <label>{{ trans('fi.default_quote_email_body') }}: </label>
                    {!! Form::textarea('setting[quoteEmailBody]', config('fi.quoteEmailBody'), ['class' => 'form-control', 'rows' => 5]) !!}
                </div>
                <div class="form-group">
                    <label>{{ trans('fi.quote_approved_email_body') }}: </label>
                    {!! Form::textarea('setting[quoteApprovedEmailBody]', config('fi.quoteApprovedEmailBody'), ['class' => 'form-control', 'rows' => 5]) !!}
                </div>
                <div class="form-group">
                    <label>{{ trans('fi.quote_rejected_email_body') }}: </label>
                    {!! Form::textarea('setting[quoteRejectedEmailBody]', config('fi.quoteRejectedEmailBody'), ['class' => 'form-control', 'rows' => 5]) !!}
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="box box-primary">
            <div class="box-header">
                <h3 class="box-title">{{ trans('fi.invoices') }}</h3>

                <div class="box-tools pull-right">
                    <span class="help-block">
                        <a href="https://www.fusioninvoice.com/docs/2020/Customization/Email-Templates#invoice-email-template"
                           target="_blank">{{ trans('fi.available_fields') }}</a>
                    </span>
                </div>
            </div>
            <div class="box-body">
                <div class="form-group">
                    <label>{{ trans('fi.invoice_email_subject') }}: </label>
                    {!! Form::text('setting[invoiceEmailSubject]', config('fi.invoiceEmailSubject'), ['class' => 'form-control']) !!}
                </div>
                <div class="form-group">
                    <label>{{ trans('fi.default_invoice_email_body') }}: </label>
                    {!! Form::textarea('setting[invoiceEmailBody]', config('fi.invoiceEmailBody'), ['class' => 'form-control', 'rows' => 5]) !!}
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="box box-primary">
            <div class="box-header">
                <h3 class="box-title">{{ trans('fi.payment_receipt') }}</h3>
            </div>
            <div class="box-body">
                <div class="form-group">
                    <label>{{ trans('fi.payment_receipt_email_subject') }}: </label>
                    {!! Form::text('setting[paymentReceiptEmailSubject]', config('fi.paymentReceiptEmailSubject'), ['class' => 'form-control']) !!}
                </div>
                <div class="form-group">
                    <label>{{ trans('fi.default_payment_receipt_body') }}: </label>
                    {!! Form::select('setting[paymentReceiptBody]', ['default' => trans('fi.default'),'custom' => trans('fi.custom')], config('fi.paymentReceiptBody'), ['class' => 'form-control']) !!}
                </div>
                <div class="form-group">
                    <label> {{ trans('fi.attach_invoice_pdf') }} </label>
                    {!! Form::checkbox('setting[paymentAttachInvoice]', 1, config('fi.paymentAttachInvoice'), ['id' => 'payment_attach_invoice', 'class' => 'adjust-checkbox']) !!}
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-6">
        <div class="box box-primary">
            <div class="box-header">
                <h3 class="box-title">{{ trans('fi.overdue_invoices') }}</h3>

                <div class="box-tools pull-right">
                    <span class="help-block d-inline-block"><a
                                href="https://www.fusioninvoice.com/docs/2020/Customization/Email-Templates#invoice-email-template"
                                target="_blank">{{ trans('fi.available_fields') }}</a></span>
                </div>
            </div>
            <div class="box-body">
                <div class="form-group">
                    <label>{{ trans('fi.overdue_email_subject') }}: </label>
                    {!! Form::text('setting[overdueInvoiceEmailSubject]', config('fi.overdueInvoiceEmailSubject'), ['class' => 'form-control']) !!}
                </div>
                <div class="form-group">
                    <label>{{ trans('fi.default_overdue_invoice_email_body') }}: </label>
                    {!! Form::textarea('setting[overdueInvoiceEmailBody]', config('fi.overdueInvoiceEmailBody'), ['class' => 'form-control', 'rows' => 5]) !!}
                </div>
                <div class="form-group">
                    <label>{{ trans('fi.overdue_invoice_reminder_frequency') }}: </label>
                    {!! Form::text('setting[overdueInvoiceReminderFrequency]', config('fi.overdueInvoiceReminderFrequency'), ['class' => 'form-control']) !!}
                    <span class="help-block">{{ trans('fi.overdue_invoice_reminder_frequency_help') }}</span>
                </div>
                <div class="form-group">
                    <label>{{ trans('fi.attach_invoice_pdf') }}</label>
                    {!! Form::checkbox('setting[overdueAttachInvoice]', 1, config('fi.overdueAttachInvoice'), ['id' => 'overdue_attach_invoice', 'class' => 'adjust-checkbox']) !!}
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="box box-primary">
            <div class="box-header">
                <h3 class="box-title">{{ trans('fi.upcoming_payment_notice') }}</h3>

                <div class="box-tools pull-right">
                    <span class="help-block"><a
                                href="https://www.fusioninvoice.com/docs/2020/Customization/Email-Templates#invoice-email-template"
                                target="_blank">{{ trans('fi.available_fields') }}</a></span>
                </div>
            </div>
            <div class="box-body">
                <div class="form-group">
                    <label>{{ trans('fi.upcoming_payment_notice_email_subject') }}: </label>
                    {!! Form::text('setting[upcomingPaymentNoticeEmailSubject]', config('fi.upcomingPaymentNoticeEmailSubject'), ['class' => 'form-control']) !!}
                </div>
                <div class="form-group">
                    <label>{{ trans('fi.upcoming_payment_notice_email_body') }}: </label>
                    {!! Form::textarea('setting[upcomingPaymentNoticeEmailBody]', config('fi.upcomingPaymentNoticeEmailBody'), ['class' => 'form-control', 'rows' => 5]) !!}
                </div>
                <div class="form-group">
                    <label>{{ trans('fi.upcoming_payment_notice_frequency') }}: </label>
                    {!! Form::text('setting[upcomingPaymentNoticeFrequency]', config('fi.upcomingPaymentNoticeFrequency'), ['class' => 'form-control']) !!}
                    <span class="help-block">{{ trans('fi.upcoming_payment_notice_frequency_help') }}</span>
                </div>
                <div class="form-group">
                    <label>{{ trans('fi.attach_invoice_pdf') }}</label>
                    {!! Form::checkbox('setting[upcomingPaymentNoticeAttachInvoice]', 1, config('fi.upcomingPaymentNoticeAttachInvoice'), ['id' => 'upcoming_payment_notice_attach_invoice', 'class' => 'adjust-checkbox']) !!}
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">

</div>
