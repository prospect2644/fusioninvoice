@include('layouts._js_chosen_email')

<script type="text/javascript">

    $(function () {

        $('#modal-mail-test').modal({backdrop: 'static'}).on('shown.bs.modal', function () {
            chosenEmailField('#from');
            chosenEmailField('#to');
            chosenEmailField('#cc');
            chosenEmailField('#bcc');
        });

        $('#btn-submit-mail-test').click(function () {

            var $btn = $(this).button('loading');

            $.post('{{ route('testMail.store') }}', {
                from: $('#from').val(),
                to: $.grep($('#to').val(), function (n) {
                    return (n);
                }),
                cc: $('#cc').val(),
                bcc: $('#bcc').val(),
                subject: $('#subject').val(),
                body: $('#body').val()
            }).done(function (response) {
                $('#modal-mail-test').modal('hide');
                if (response.success == true) {
                    alertify.success(response.message);
                } else {
                    alertify.defaults.theme.ok = "ui negative button";
                    var alert = '<span style="color:white;"> '
                            + '<?php echo trans('{!! fi.email-test-failed !!}'); ?>' + '</span>';
                    $("<style>").text(".ajs-content{text-overflow: ellipsis;width: 100%;overflow: hidden; }").appendTo($("body"));

                    window.showAlert = function () {
                        alertify.alert(response.message);
                    }

                    alertify.alert().setting('modal', true).setHeader(alert).set('defaultFocus', 'ok');
                    window.showAlert();
                }
                $btn.button('reset');
            }).fail(function (response) {
                $btn.button('reset');
                showAlertifyErrors($.parseJSON(response.responseText).errors);
            });
        });

    });

</script>