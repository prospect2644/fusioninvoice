<script type="text/javascript">

    $(function () {

        $("#invoice_date").datepicker({format: '{{ config('fi.datepickerFormat') }}', autoclose: true});
        $("#due_at").datepicker({format: '{{ config('fi.datepickerFormat') }}', autoclose: true});
        $('#invoice-tags').select2({tags: true, tokenSeparators: [",", " "]});
        $('textarea').autosize();

        $(document).on('change', '.item-lookup', function () {
            var row = $(this).closest('tr');
            row.find('.lbl_item_lookup > .update_item_lookup').prop("checked", false);
        });

        $(document).on('change', 'textarea[name="description"],input[name="price"],select[name="tax_rate_id"],select[name="tax_rate_2_id"]', function () {
            var row = $(this).closest('tr');
            row.find('.lbl_item_lookup').show();
            if (!row.find('.lbl_item_lookup > .update_item_lookup').prop("checked") && typeof row.find('.item-lookup option:selected').val() != "undefined" && row.find('.item-lookup option:selected').val() != row.find('.item-lookup option:selected').text()) {
                row.find('.lbl_item_lookup').show().html('<input type="checkbox" class="update_item_lookup" name="save_item_as_lookup" tabindex="999"> {{ trans('fi.update_item_as_lookup') }}');
            }
        });

        $('#btn-copy-invoice').click(function () {
            $('#modal-placeholder').load('{{ route('invoiceCopy.create') }}', {
                invoice_id: '{{ $invoice->id }}'
            });
        });

        $('#btn-update-exchange-rate').click(function () {
            updateExchangeRate();
        });

        $('#currency_code').change(function () {
            updateExchangeRate();
        });

        function updateExchangeRate() {

            if ($('#currency_code').val() != '{{ config('fi.baseCurrency') }}') {
                $('#currency_code, #exchange_rate').css('background', '#fff8dc');
            } else {
                $('#currency_code, #exchange_rate').css('background', 'none');
            }

            $.post('{{ route('currencies.getExchangeRate') }}', {
                currency_code: $('#currency_code').val()
            }, function (data) {
                $('#exchange_rate').val(data);
            });
        }

        $('.btn-save-invoice').click(function () {
            var items = [];
            var display_order = 1;
            var custom_fields = {};
            var apply_exchange_rate = $(this).data('apply-exchange-rate');
            var files = [];
            var invoice_data = {};
            var form_data;

            var $btn = $(this).button('loading');

            $('table tr.item').each(function () {
                let qty = ($(this).find('input[name="quantity"]').eq(0).val());
                let name = ($(this).find('select[name="name"] option:selected').eq(0).text());
                let price = ($(this).find('input[name="price"]').eq(0).val());
                if (qty && name && price) {
                    var row = {};
                    $(this).find('input,select,textarea').each(function () {
                        if ($(this).attr('name') !== undefined) {
                            if ($(this).is(':checkbox')) {
                                if ($(this).is(':checked')) {
                                    row[$(this).attr('name')] = 1;
                                }
                                else {
                                    row[$(this).attr('name')] = 0;
                                }
                            }
                            else {
                                if ($(this).attr('name') == 'name') {
                                    row[$(this).attr('name')] = name;
                                } else {
                                    row[$(this).attr('name')] = $(this).val();
                                }

                            }
                        }
                    });
                    row['display_order'] = display_order;
                    display_order++;
                    items.push(row);
                }
            });
            $('.custom-form-field').each(function () {
                var fieldName = $(this).data('invoices-field-name');
                var inputType = $(this).attr('type') || this.tagName.toLowerCase();
                if (fieldName !== undefined) {
                    if ('file' === inputType) {
                        custom_fields[fieldName] = typeof this.files[0] === 'undefined' ? '' : this.files[0];

                        return true;
                    }

                    if ('select' === inputType) {
                        if ($(this).find('option:selected').length == 0) {
                            custom_fields[fieldName] = '';
                            return true;
                        }
                    }
                    if ('checkbox' === inputType) {
                        custom_fields[fieldName] = ($(this).is(":checked")) ? 1 : 0;
                        return true;
                    }

                    custom_fields[fieldName] = $(this).val();
                }
            });

            invoice_data['number'] = $('#number').val();
            invoice_data['invoice_date'] = $('#invoice_date').val();
            invoice_data['due_at'] = $('#due_at').val();
            invoice_data['status'] = $('#status').val();
            invoice_data['items'] = items;
            invoice_data['terms'] = $('#terms').val();
            invoice_data['footer'] = $('#footer').val();
            invoice_data['currency_code'] = $('#currency_code').val();
            invoice_data['exchange_rate'] = $('#exchange_rate').val();
            invoice_data['custom'] = custom_fields;
            invoice_data['apply_exchange_rate'] = typeof apply_exchange_rate === 'undefined' ? '' : apply_exchange_rate;
            invoice_data['template'] = $('#template').val();
            invoice_data['summary'] = $('#summary').val();
            invoice_data['discount'] = $('#discount').val();
            invoice_data['custom_files'] = files;
            invoice_data['tags'] = $('#invoice-tags').val();

            form_data = objectToFormData(invoice_data);
            $.ajax({
                url: '{{ route('invoices.update', [$invoice->id]) }}',
                method: 'post',
                data: form_data,
                processData: false,
                contentType: false,
                success: function (data) {
                    if (data.error) {
                        alertify.error(data.error, 5);
                    }
                },
            }).done(function () {
                $('#div-invoice-edit').load('{{ route('invoiceEdit.refreshEdit', [$invoice->id]) }}', function () {
                    alertify.success('{{ trans('fi.record_successfully_updated') }}', 5);
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
                $.each($.parseJSON(response.responseText).errors, function (id, message) {
                    alertify.error(message[0], 5);
                });
            });
        });

        var fixHelper = function (e, tr) {
            var $originals = tr.children();
            var $helper = tr.clone();
            $helper.children().each(function (index) {
                $(this).width($originals.eq(index).width())
            });
            return $helper;
        };

        $("#item-table tbody").sortable({
            helper: fixHelper,
            handle: ".handle"
        });

        $('#btn-delete-custom-img').click(function () {
            var url = "{{ route('invoiceEdit.deleteImage', [$invoice->id,'field_name' => '']) }}";
            $.post(url + '/' + $(this).data('field-name')).done(function () {
                $('.custom_img').html('');
            });
        });

        alertify.defaults.theme.ok = "ui negative button";
        alertify.defaults.theme.cancel = "ui black button";
        $("<style>").text(".ajs-header{ background-color: #ba0606 !important; }").appendTo($("body"));

        $(document).on('click', '.btn-delete-invoice-item', function () {
            var id = $(this).data('item-id');
            if (typeof id === 'undefined') {
                $(this).closest('tr').remove();
            } else {
                alertify.confirm('{{ trans('fi.delete_record_warning') }}', function () {
                    $.post('{{ route('invoiceItem.delete') }}', {
                        id: id
                    }).done(function () {
                        $('#tr-item-' + id).remove();
                        $('#div-totals').load('{{ route('invoiceEdit.refreshTotals') }}', {
                            id: '{{ $invoice->id }}'
                        });
                    });
                }, function () {
                    alertify.alert().destroy();
                }).setHeader(confirmHeader).set({transition: 'zoom', defaultFocus: 'cancel'});
            }

        });

        $('.btn-delete-invoice').click(function () {
            alertify.confirm("{!! trans('fi.delete_record_warning') !!}", function () {
                $.ajax({
                    url: '{{ route('invoices.delete', [$invoice->id]) }}',
                    method: 'get',
                    beforeSend: function () {
                        $(".modal-loader").show();
                    },
                    success: function () {
                        alertify.success('{{ trans('fi.record_successfully_deleted') }}', 5);
                        window.location.replace('{{ $returnUrl }}');
                    }
                });
            }, function () {
                alertify.alert().destroy();
            }).setHeader(confirmHeader).set({transition: 'zoom', defaultFocus: 'cancel'});
        });

        $('.apply-credit-memo').click(function () {
            var url = '{{ route("payments.prepareInvoiceSettlementWithCreditMemo", ":invoice") }}';
            url = url.replace(':invoice', $(this).data('invoice-id'));
            var redirect_url = $(this).data('redirect-to');
            $('#modal-placeholder').load(url, {
                redirect_to: redirect_url
            });
        });

        $('.apply-pre-payment').click(function () {
            var url = '{{ route("payments.prepareInvoiceSettlementWithPrePayment", ":invoice") }}';
            url = url.replace(':invoice', $(this).data('invoice-id'));
            var redirect_url = $(this).data('redirect-to');
            $('#modal-placeholder').load(url, {
                redirect_to: redirect_url
            });
        });

        $('.apply-to-invoices').click(function () {
            var url = '{{ route("payments.prepareCreditApplication", ":creditMemo") }}';
            url = url.replace(':creditMemo', $(this).data('invoice-id'));
            var redirect_url = $(this).data('redirect-to');
            $('#modal-placeholder').load(url, {
                redirect_to: redirect_url
            });
        });

        // When balance is nonzero then we have to disabled paid status
        var balance = '{{ $invoice->amount->balance }}';
        if (balance > 0) {
            $("#status option[value='paid']").prop('disabled', true);
        }

        $('#btn-print-invoice').click(function () {
            $.get($(this).data('action')).done(function (response) {
                window.open(response).print();
            });
        });
    });

</script>
