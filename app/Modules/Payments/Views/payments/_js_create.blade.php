<script type="text/javascript">

    $(function () {
        var importantNoteHeader = '<span style="color:white;"> <span class="fa fa-bell-o fa-2x"'
                + 'style="vertical-align:middle;padding-right:10px;">'
                + '</span>' + '{!! trans('fi.important') !!}' + '</span>';

        $('#modal-enter-payment').modal();

        $("#payment_date").datepicker({
            format: '{{ config('fi.datepickerFormat') }}',
            autoclose: true,
            todayHighlight: true
        });


        function countRemainingBalance() {
            let invoice_balance = parseFloat($('#invoice_balance').data('amount'));
            let entered_amount = parseFloat(currencyUnformat($('#payment_amount').val(), $('#currency_code').val()));
            let remaining_balance = (invoice_balance - entered_amount).toFixed(2);
            return remaining_balance;
        }

        $("#payment_amount").blur(function () {
            var remaining_balance = countRemainingBalance();
            if (remaining_balance < 0) {
                $('#remaining_balance').val(currencyFormat(0.00, $('#currency_code').val()));
            }
            else {
                $('#remaining_balance').val(currencyFormat(remaining_balance, $('#currency_code').val()));
            }
        });
        @can('payments.create')
        $('#enter-payment-confirm').click(function () {
                    let entered_amount = parseFloat(standardCurrencyFormat($('#payment_amount').val()));
                    if (entered_amount <= 0) {
                        alertify.alert().setHeader(importantNoteHeader).set({transition: 'zoom'})
                                .setContent("{!! trans('fi.payment_warning') !!}").showModal();
                        return false;
                    }
                    var custom_fields = {};

                    var $btn = $(this).button('loading');

                    $('#payment-custom-fields .custom-form-field').each(function () {
                        custom_fields[$(this).data('payments-field-name')] = $(this).val();
                    });

                    $.post('{{ route('payments.store') }}', {
                        client_id: $('#client_id').val(),
                        invoice_id: $('#invoice_id').val(),
                        amount: $('#payment_amount').val(),
                        remaining_balance: $('#remaining_balance').val(),
                        payment_method_id: $('#payment_method_id').val(),
                        paid_at: $('#payment_date').val(),
                        note: $('#payment_note').val(),
                        custom: custom_fields,
                        email_payment_receipt: ($('#email_payment_receipt').prop('checked')) ? 1 : 0,
                        currency_code: $('#currency_code').val()
                    }).done(function () {
                        window.location = '{!! $redirectTo !!}';
                    }).fail(function (response) {
                        $btn.button('reset');
                        showAlertifyErrors($.parseJSON(response.responseText).errors);
                    });
                });
        @endcan

    });

</script>
