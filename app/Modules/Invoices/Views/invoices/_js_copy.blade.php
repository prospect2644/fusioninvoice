<script type="text/javascript">

    $(function () {

        $('#modal-copy-invoice').modal();

        $('#modal-copy-invoice').on('shown.bs.modal', function () {
            $("#client_name").focus();
        });

        $("#copy_invoice_date").datepicker({format: '{{ config('fi.datepickerFormat') }}', autoclose: true});

        // Creates the invoice
        $('#btn-copy-invoice-submit').click(function () {
            $.post('{{ route('invoiceCopy.store') }}', {
                invoice_id: '{{ $invoice->id }}',
                client_name: $('#copy_client_name').val(),
                company_profile_id: $('#copy_company_profile_id').val(),
                invoice_date: $('#copy_invoice_date').val(),
                document_number_scheme_id: $('#copy_document_number_scheme_id').val(),
                user_id: '{{ $user_id }}',
                type: '{{ $invoice->type }}'
            }).done(function (response) {
                window.location = '{{ url('invoices') }}' + '/' + response.id + '/edit';
            }).fail(function (response) {
                showAlertifyErrors($.parseJSON(response.responseText).errors);
            });
        });
    });

</script>