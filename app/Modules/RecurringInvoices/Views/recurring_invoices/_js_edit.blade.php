<script type="text/javascript">

    $(function () {

        $("#next_date").datepicker({format: '{{ config('fi.datepickerFormat') }}', autoclose: true});
        $("#stop_date").datepicker({format: '{{ config('fi.datepickerFormat') }}', autoclose: true});
        $('#invoice-tags').select2({tags: true, tokenSeparators: [",", " "]});
        $('textarea').autosize();

        $('#btn-copy-recurring-invoice').click(function () {
            $('#modal-placeholder').load('{{ route('recurringInvoiceCopy.create') }}', {
                recurring_invoice_id: '{{ $recurringInvoice->id }}'
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

        $('.btn-save-recurring-invoice').click(function () {
            var items = [];
            var display_order = 1;
            var custom_fields = {};
            var apply_exchange_rate = $(this).data('apply-exchange-rate');
            var files = [];
            var recurring_invoice_data = {};
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
                var fieldName = $(this).data('recurring_invoices-field-name');
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
            recurring_invoice_data['items'] = items;
            recurring_invoice_data['terms'] = $('#terms').val();
            recurring_invoice_data['footer'] = $('#footer').val();
            recurring_invoice_data['currency_code'] = $('#currency_code').val();
            recurring_invoice_data['exchange_rate'] = $('#exchange_rate').val();
            recurring_invoice_data['custom'] = custom_fields;
            recurring_invoice_data['apply_exchange_rate'] = typeof apply_exchange_rate === 'undefined' ? '' : apply_exchange_rate;
            recurring_invoice_data['template'] = $('#template').val();
            recurring_invoice_data['summary'] = $('#summary').val();
            recurring_invoice_data['discount'] = $('#discount').val();
            recurring_invoice_data['next_date'] = $('#next_date').val();
            recurring_invoice_data['stop_date'] = $('#stop_date').val();
            recurring_invoice_data['recurring_frequency'] = $('#recurring_frequency').val();
            recurring_invoice_data['recurring_period'] = $('#recurring_period').val();
            recurring_invoice_data['document_number_scheme_id'] = $('#document_number_scheme_id').val();
            recurring_invoice_data['custom_files'] = files;
            recurring_invoice_data['tags'] = $('#invoice-tags').val();

            form_data = objectToFormData(recurring_invoice_data);
            $.ajax({
                url: '{{ route('recurringInvoices.update', [$recurringInvoice->id]) }}',
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
                $('#div-recurring-invoice-edit').load('{{ route('recurringInvoiceEdit.refreshEdit', [$recurringInvoice->id]) }}', function () {
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
            helper: fixHelper
        });

        $('#btn-delete-custom-img').click(function () {
            var url = "{{ route('recurringInvoiceEdit.deleteImage', [$recurringInvoice->id,'field_name' => '']) }}";
            $.post(url + '/' + $(this).data('field-name')).done(function () {
                $('.custom_img').html('');
            });
        });

        alertify.defaults.theme.ok = "ui negative button";
        alertify.defaults.theme.cancel = "ui black button";
        $("<style>").text(".ajs-header{ background-color: #ba0606 !important; }").appendTo($("body"));

        $('.btn-delete-recurring-invoice').click(function () {

            alertify.confirm('{{ trans('fi.delete_record_warning') }}', function () {
                window.location = decodeURIComponent('{{ route('recurringInvoices.delete', [$recurringInvoice->id]) }}');
            }, function () {
                alertify.alert().destroy();
            }).setHeader(confirmHeader).set({transition: 'zoom', defaultFocus: 'cancel'});
        });

        $(document).on('click', '.btn-delete-recurring-invoice-item', function () {
            var id = $(this).data('item-id');
            if (typeof id === 'undefined') {
                $(this).closest('tr').remove();
            } else {
                alertify.confirm('{{ trans('fi.delete_record_warning') }}', function () {
                    $.post('{{ route('recurringInvoiceItem.delete') }}', {
                        id: id
                    }).done(function () {
                        $('#tr-item-' + id).remove();
                        $('#div-totals').load('{{ route('recurringInvoiceEdit.refreshTotals') }}', {
                            id: '{{ $recurringInvoice->id }}'
                        });
                    });
                }, function () {
                    alertify.alert().destroy();
                }).setHeader(confirmHeader).set({transition: 'zoom', defaultFocus: 'cancel'});
            }

        });

    });

</script>