<script type="text/javascript">

    $(function () {

        $('#modal-edit-client').modal();

        $('#form-edit-client').on('submit', function (e) {

            e.preventDefault();
            $.post(this.action, $(this).serialize())
                .done(function () {
                    $('#modal-edit-client').modal('hide');
                    $('#col-to').load('{{ $refreshToRoute }}', {
                        id: {{ $id }}
                    });
                })
                .fail(function (response) {
                    showAlertifyErrors($.parseJSON(response.responseText).errors);
                });
        });
    });

</script>