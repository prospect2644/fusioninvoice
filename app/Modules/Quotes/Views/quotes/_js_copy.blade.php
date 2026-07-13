<script type="text/javascript">

    $(function () {

        $('#modal-copy-quote').modal();

        $('#modal-copy-quote').on('shown.bs.modal', function () {
            $("#client_name").focus();
        });

        $("#copy_quote_date").datepicker({format: '{{ config('fi.datepickerFormat') }}', autoclose: true});

        // Creates the quote
        $('#btn-copy-quote-submit').click(function () {
            $.post('{{ route('quoteCopy.store') }}', {
                quote_id: '{{ $quote->id }}',
                client_name: $('#copy_client_name').val(),
                company_profile_id: $('#copy_company_profile_id').val(),
                quote_date: $('#copy_quote_date').val(),
                document_number_scheme_id: $('#copy_document_number_scheme_id').val(),
                user_id: '{{ $user_id }}'
            }).done(function (response) {
                window.location = '{{ url('quotes') }}' + '/' + response.id + '/edit';
            }).fail(function (response) {
                showAlertifyErrors($.parseJSON(response.responseText).errors);
            });
        });
    });

</script>