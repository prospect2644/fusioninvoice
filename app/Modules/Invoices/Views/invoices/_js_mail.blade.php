@include('layouts._js_chosen_email')

<script type="text/javascript">

    $(function () {

        var attachPdf = 0;

        $('#modal-mail-invoice').modal({backdrop: 'static'}).on('shown.bs.modal', function () {
            chosenEmailField('#to');
            chosenEmailField('#cc');
            chosenEmailField('#bcc');
            chosenEmailField('#mail_from');
        });

        $('#btn-submit-mail-invoice').click(function () {

            var $btn = $(this).button('loading');

            if ($('#attach_pdf').prop('checked') == true) {
                attachPdf = 1;
            }

            $.post('{{ route('invoiceMail.store') }}', {
                invoice_id: '{{ $invoice->id }}',
                mail_from: $('#mail_from').val(),
                to: $('#to').val(),
                cc: $('#cc').val(),
                bcc: $('#bcc').val(),
                subject: $('#subject').val(),
                body: $('#body').val(),
                attach_pdf: attachPdf
            }).done(function () {
                $('#modal-mail-invoice').modal('hide');
                $('#div-invoice-edit').load('{{ route('invoiceEdit.refreshEdit', [$invoice->id]) }}', function () {
                    alertify.success('{{ trans('fi.email_sent') }}', 5);
                    var settings = {
                        placeholder: '{{ trans('fi.select-item') }}',
                        allowClear: true,
                        tags: true,
                        selectOnClose: true
                    };
                    // Make all existing items select
                    $('.item-lookup').select2(settings);
                });
            }).fail(function (response) {
                $btn.button('reset');
                showAlertifyErrors($.parseJSON(response.responseText).errors);
            });
        });

    });

</script>
