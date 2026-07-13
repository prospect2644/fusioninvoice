<script type="text/javascript">

    $(function () {

        $('#create-invoice').modal({backdrop: 'static'});

        $('#create-invoice').on('shown.bs.modal', function () {
            $("#create_client_name").focus();
        });

        $('#create_invoice_date').datepicker({format: '{{ config('fi.datepickerFormat') }}', autoclose: true});

        $('#invoice-create-confirm').click(function () {

            $.post('{{ route('invoices.store') }}', {
                type: $('input:radio[name="type"]:checked').val(),
                user_id: $('#user_id').val(),
                company_profile_id: $('#company_profile_id').val(),
                client_name: $('#create_client_name').val(),
                invoice_date: $('#create_invoice_date').val(),
                document_number_scheme_id: $('#create_document_number_scheme_id').val()
            }).done(function (response) {
                window.location = '{{ url('invoices') }}' + '/' + response.id + '/edit';
            }).fail(function (response) {
                showAlertifyErrors($.parseJSON(response.responseText).errors);
            });
        });

        $('input:radio[name="type"]').change(function () {
            let type = $(this).val();
            if(type == 'invoice'){
                $('#invoice_create_title').html("{{ trans('fi.create_invoice') }}");
                setDocumentNumberOption('invoice');
            }
            else if(type == 'credit_memo'){
                $('#invoice_create_title').html("{{ trans('fi.create_credit_memo') }}");
                setDocumentNumberOption('credit_memo');
            }
        })
        function setDocumentNumberOption(documentType) {
            let documentNumbers = @json($documentNumberSchemes);
            let particularDocument = documentNumbers[documentType];
            $('#create_document_number_scheme_id option').remove();
            for (let [id, name] of Object.entries(particularDocument)) {
                $("#create_document_number_scheme_id").append(new Option(name, id));
            }
        }
    });

</script>