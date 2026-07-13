<script type="text/javascript">

    $(function () {

        $('#modal-copy-recurring-invoice').modal();

        $('#modal-copy-recurring-invoice').on('shown.bs.modal', function () {
            $("#client_name").focus();
        });

        $('#copy_next_date').datepicker({format: '{{ config('fi.datepickerFormat') }}', autoclose: true});
        $('#copy_stop_date').datepicker({format: '{{ config('fi.datepickerFormat') }}', autoclose: true});

        // Creates the recurringInvoice
        $('#btn-copy-recurring-invoice-submit').click(function () {
            $.post('{{ route('recurringInvoiceCopy.store') }}', {
                recurring_invoice_id: '{{ $recurringInvoice->id }}',
                client_name: $('#copy_client_name').val(),
                company_profile_id: $('#copy_company_profile_id').val(),
                document_number_scheme_id: $('#copy_document_number_scheme_id').val(),
                user_id: '{{ auth()->user()->id }}',
                next_date: $('#copy_next_date').val(),
                recurring_frequency: $('#copy_recurring_frequency').val(),
                recurring_period: $('#copy_recurring_period').val(),
                stop_date: $('#copy_stop_date').val()
            }).done(function (response) {
                window.location = response.url;
            }).fail(function (response) {
                if (response.status == 400) {
                    showAlertifyErrors($.parseJSON(response.responseText).errors);
                }
                else {
                    alertify.error('{{ trans('fi.unknown_error') }}', 5);
                }
            });
        });
    });

</script>