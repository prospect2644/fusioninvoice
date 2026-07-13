<script src='{{ asset('assets/plugins/validate_numeric_input.js') }}'></script>
<style>
    .invoice-table {
        max-height: 300px;
        overflow-y: scroll;
        overflow-x: hidden;
    }

    ::-webkit-scrollbar {
        -webkit-appearance: none;
        width: 7px;
    }

    ::-webkit-scrollbar-thumb {
        border-radius: 4px;
        background-color: rgba(0, 0, 0, .5);
        box-shadow: 0 0 1px rgba(255, 255, 255, .5);
    }
</style>
<script type="text/javascript">
    $(function () {
        alertify.defaults.theme.ok = "ui negative button";
        alertify.defaults.theme.cancel = "ui black button";

        var importantNoteHeader = '<span style="color:white;"> <span class="fa fa-bell-o fa-2x"'
                + 'style="vertical-align:middle;padding-right:10px;">'
                + '</span>' + '{!! trans('fi.important') !!}' + '</span>';

        $("<style>").text(".ajs-header{ background-color: #ba0606 !important; }").appendTo($("body"));

        function submitFetchInvoicesForm() {
            $.post('{{ route('payments.storeCreditApplication') }}', $('#fetch-invoices-form').serialize(), {}).done(function (response) {
                if (response.success == true) {
                    $("#modal-fetch-invoices").modal("hide");
                    window.location = '{{ $redirectTo }}';
                } else {
                    showAlertifyErrors($.parseJSON(response.responseText).message);
                }
            }).fail(function (response) {
                showAlertifyErrors($.parseJSON(response.responseText).message);
            });
        }

        $('#modal-fetch-invoices').modal();

        $('#confirm-payment-invoices').on("click", function () {
            let that = $(this);
            let remaining_balance = countRemainingBalance();

            if (remaining_balance <= 0) {
                alertify.defaults.theme.cancel = "hide";
                $("<style>").text(".ajs-header, .ui.negative.button{ background-color: #00a65a !important; }").appendTo($("body"));
                var confirm_remaining_balance = "{!! trans('fi.confirm_full_payment_applied') !!}";
                var header = remainZeroCreditMemoBalanceHeader;
            } else {
                $("<style>").text(".ajs-header, .ui.negative.button{ background-color: #ba0606 !important; }").appendTo($("body"));
                var confirm_remaining_balance = "{!! trans('fi.confirm_remaining_balance') !!}";
                confirm_remaining_balance = confirm_remaining_balance.replace(':value', "{{getCurrencySign($creditMemo->currency_code)}}" + ' ' + systemCurrencyFormat(remaining_balance));
                var header = remainCreditMemoBalanceHeader;
            }

            if ($("input:checkbox:checked[id^='invoice_selection_']").length > 0) {
                if (parseFloat(remaining_balance)) {
                    alertify.confirm(confirm_remaining_balance, function () {
                        that.prop("disabled", true).html(that.data('loading-text'));
                        submitFetchInvoicesForm();
                    }, function () {
                        alertify.alert().destroy();
                    }).setHeader(header).set({transition: 'zoom', defaultFocus: 'ok'});
                } else {
                    that.prop("disabled", true).html(that.data('loading-text'));
                    submitFetchInvoicesForm();
                }
            } else {
                alertify.confirm("{!! trans('fi.invoice_not_selected_warning') !!}", function () {
                    that.prop("disabled", true).html(that.data('loading-text'));
                }, function () {
                    alertify.alert().destroy();
                }).setHeader(confirmHeader).set({
                    transition: 'zoom',
                    defaultFocus: 'cancel',
                    'labels': {ok: 'Yes', cancel: 'No'}
                });
            }

        });

        const selectInvoice = (invoice_id) => {

            return new Promise((resolve, reject) => {

            let currency = $('#invoice_selection_' + invoice_id).data('currency');
            let fetch_full_amount = parseFloat($('#invoice_selection_' + invoice_id).data('amount'));

            let remaining_balance = parseFloat($('#remaining_balance').data('amount'));

            if ($('#invoice_selection_' + invoice_id).prop("checked")) {
                if (remaining_balance >= fetch_full_amount) {
                    $('#paid_amount_' + invoice_id).val(currencyFormat(fetch_full_amount.toFixed(2), currency)).prop("disabled", false);
                } else {
                    $('#paid_amount_' + invoice_id).val(currencyFormat(remaining_balance, currency)).prop("disabled", false);
                }
            } else {

                $('#paid_amount_' + invoice_id).val(currencyFormat(0.00, currency)).prop("disabled", true);
            }
            resolve();
        });
    };

    $("input:checkbox[id^='invoice_selection_']").click(function () {
        selectInvoice($(this).data('id')).then(() => countRemainingBalance());
    });

    $("input[id^='paid_amount_']").blur(function () {
        let invoice_amount = parseFloat(standardCurrencyFormat($(this).data('amount')));
        let entered_amount = parseFloat(standardCurrencyFormat($(this).val()));
        if (entered_amount > invoice_amount) {
            $(this).val(systemCurrencyFormat(0.00));
            alertify.alert().setHeader(importantNoteHeader).set({transition: 'zoom'})
                    .setContent("{!! trans('fi.more_figure_then_invoice_amount') !!}").showModal();
        }
        var remaining_balance = countRemainingBalance();
        if (remaining_balance < 0) {
            $(this).val(systemCurrencyFormat(0.00));
            alertify.alert().setHeader(importantNoteHeader).set({transition: 'zoom'})
                    .setContent("{!! trans('fi.more_figure_then_total_payment') !!}").showModal();
            countRemainingBalance();
        }
    });

        function countRemainingBalance() {
            let total_paid = 0.00;
            let currency = $('#amount').data('currency');
            let entered_amount = parseFloat($('#amount').data('amount')).toFixed(2);
            $("input:checkbox:checked[id^='invoice_selection_']").each(function () {
                let invoice_id = $(this).data('id');
                if ($('#paid_amount_' + invoice_id).val()) {
                    let current_paid_amount = currencyUnformat($('#paid_amount_' + invoice_id).val(), $('#paid_amount_' + invoice_id).data('currency'));
                    total_paid = parseFloat(total_paid) + parseFloat(current_paid_amount);
                }
            });
            let remaining_balance = (entered_amount - total_paid).toFixed(2);

            $('#remaining_balance').val(currencyFormat(remaining_balance, currency));
            $('#remaining_balance').data('amount', remaining_balance);
            $('#total_paid').val(total_paid.toFixed(2));
            return remaining_balance;
        }
    });

</script>
