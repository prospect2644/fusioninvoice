<script type="text/javascript">

    var all_currencies = @json(isset($allCurrencies) ? $allCurrencies : []);

    function currencyUnformat(input, currency){
        let matchCurrency = all_currencies.find(c => c.code == currency);
        let cleanInput = input;
        cleanInput = cleanInput.replaceAll(matchCurrency.decimal, 'D');
        cleanInput = cleanInput.replaceAll(matchCurrency.thousands, '');
        cleanInput = cleanInput.replaceAll('D', '.');
        return cleanInput;
    }

    function currencyFormat(input, currency){
        let matchCurrency = all_currencies.find(c => c.code == currency);
        let clean_input = (input.toString().includes('.')) ? input.toString() : input + '.00';
        var num_parts = clean_input.split(".");
        num_parts[0] = num_parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, matchCurrency.thousands);
        return num_parts.join(matchCurrency.decimal);
    }

    function showAlertifyErrors(errors) {

        if (errors == null) {
            return;
        }

        $.each(errors, function (id, message) {
            alertify.error(message[0], 5);
        });

    }

    function showErrors(errors, placeholder) {

        $('.input-group.has-error').removeClass('has-error');
        $(placeholder).html('');
        if (errors == null && placeholder) {
            return;
        }

        $.each(errors, function (id, message) {
            if (id) $('#' + id).parents('.input-group').addClass('has-error');
            if (placeholder) $(placeholder).append('<div class="alert alert-danger">' + message[0] + '</div>');
        });

    }

    function clearErrors() {
        $('.input-group.has-error').removeClass('has-error');
    }

    $(function () {

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        @can('quotes.create')
        $('.create-quote').click(function () {
                    var clientName = $(this).data('unique-name');
                    $('#modal-placeholder').load('{{ route('quotes.create') }}', function () {
                        $('#create_client_name').val(clientName).trigger('change')
                    });
                });
        @endcan

        @can('invoices.create')
        $('.create-invoice').click(function () {
                    var clientName = $(this).data('unique-name');
                    $('#modal-placeholder').load('{{ route('invoices.create') }}', function () {
                        $('#create_client_name').val(clientName).trigger('change')
                    });
                });
        @endcan

        @can('payments.create')
        $('.create-payment').click(function () {
                    $('#modal-placeholder').load('{{ route('payments.createPayment') }}');
                });
        @endcan

        @can('payments.update')
        $('.edit-payment').click(function () {
                    $('#modal-placeholder').load($(this).data('action'));
                });
        @endcan

        @can('invoices.view')
        $('.payment-applications').click(function () {
                    $('#modal-placeholder').load($(this).data('action'));
                });
        @endcan

        @can('recurring_invoices.create')
        $('.create-recurring-invoice').click(function () {
                    var clientName = $(this).data('unique-name');
                    $('#modal-placeholder').load('{{ route('recurringInvoices.create') }}', function () {
                        $('#create_client_name').val(clientName).trigger('change')
                    });
                });
        @endcan

        $(document).on('click', '.email-quote', function () {
                    $('#modal-placeholder').load('{{ route('quoteMail.create') }}', {
                        quote_id: $(this).data('quote-id'),
                        redirectTo: $(this).data('redirect-to')
                    }, function (response, status, xhr) {
                        if (status == 'error') {
                            alertify.error('{{ trans('fi.problem_with_email_template') }}');
                        }
                    });
                });

        $(document).on('click', '.email-invoice', function () {
            $('#modal-placeholder').load('{{ route('invoiceMail.create') }}', {
                invoice_id: $(this).data('invoice-id'),
                redirectTo: $(this).data('redirect-to')
            }, function (response, status, xhr) {
                if (status == 'error') {
                    alertify.error('{{ trans('fi.problem_with_email_template') }}');
                }
            });
        });

        $(document).on('click', '#user_notification', function () {
            $('#modal-placeholder').load('{{ route('notifications.userNotifications') }}', function (response, status, xhr) {
                if (status == 'error') {
                    alertify.error('{{ trans('fi.problem_with_email_template') }}');
                }
            });
        });

        @can('payments.create')
        $(document).on('click', '.enter-payment', function () {
                    $('#modal-placeholder').load('{{ route('payments.create') }}', {
                        invoice_id: $(this).data('invoice-id'),
                        invoice_balance: $(this).data('invoice-balance'),
                        redirectTo: $(this).data('redirect-to')
                    });
                });
        @endcan

        $('#bulk-select-all').click(function () {
                    if ($(this).prop('checked')) {
                        $('.bulk-record').prop('checked', true);
                        if ($('.bulk-record:checked').length > 0) {
                            $('.bulk-actions').show();
                        }
                    }
                    else {
                        $('.bulk-record').prop('checked', false);
                        $('.bulk-actions').hide();
                    }
                });

        $('.bulk-record').click(function () {
            if ($('.bulk-record:checked').length > 0) {
                $('.bulk-actions').show();
            }
            else {
                $('.bulk-actions').hide();
                $('#bulk-select-all').prop('checked', false);
            }

            if ($(this).prop('checked')) {
                var isAllChecked = 1;

                $('.bulk-record').each(function () {
                    if (!this.checked)
                        isAllChecked = 0;
                });

                if (isAllChecked == 1) {
                    $('#bulk-select-all').prop('checked', true);
                }
            } else {
                $('#bulk-select-all').prop('checked', false);
            }
        });

        $('.bulk-actions').hide();

    });

    function resizeIframe(obj, minHeight) {
        obj.style.height = '';
        var height = obj.contentWindow.document.body.scrollHeight;

        if (height < minHeight) {
            obj.style.height = minHeight + 'px';
        }
        else {
            obj.style.height = (height + 50) + 'px';
        }
    }

    function resizeIframeSection(obj, minHeight) {
        obj.style.height = '';
        var height = obj.contentWindow.document.body.scrollHeight;
        if (height < minHeight) {
            height = minHeight + 'px';
        }
        else {
            height = (height + 95) + 'px';
        }
        $('.iframe-content').css("height", height);
    }

    function standardCurrencyFormat(value) {
        @if(config('fi.baseCurrency') == 'EUR')
            return value.toString().replace(",", ".");
        @endif
        return value.replace(",", ".");
    }

    function systemCurrencyFormat(value) {

        @if(config('fi.baseCurrency') == 'EUR')
            return value.toString().replace(".", ",");
        @endif

        return value;
    }

    function printPdf(url) {
        var iframe = this._printIframe;
        if (!this._printIframe) {
            iframe = this._printIframe = document.createElement('iframe');
            document.body.appendChild(iframe);

            iframe.style.display = 'none';
            iframe.onload = function () {
                setTimeout(function () {
                    iframe.focus();
                    iframe.contentWindow.print();
                }, 1);
            };
        }
        iframe.src = url;
    }

    (function ($) {
        $.fn.serializeFormJSON = function () {

            var o = {};
            var a = this.serializeArray();
            $.each(a, function () {
                if (o[this.name]) {
                    if (!o[this.name].push) {
                        o[this.name] = [o[this.name]];
                    }
                    o[this.name].push(this.value || '');
                } else {
                    o[this.name] = this.value || '';
                }
            });
            return o;
        };
    })(jQuery);
</script>
