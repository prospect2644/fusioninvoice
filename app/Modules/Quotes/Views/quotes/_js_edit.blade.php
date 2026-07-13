<script type="text/javascript">

    $(function () {

        $("#quote_date").datepicker({format: '{{ config('fi.datepickerFormat') }}', autoclose: true});
        $("#expires_at").datepicker({format: '{{ config('fi.datepickerFormat') }}', autoclose: true});
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

        $('#btn-copy-quote').click(function () {
            $('#modal-placeholder').load('{{ route('quoteCopy.create') }}', {
                quote_id: '{{ $quote->id }}'
            });
        });

        $('#btn-quote-to-invoice').click(function () {
            $('#modal-placeholder').load('{{ route('quoteToInvoice.create') }}', {
                quote_id: '{{ $quote->id }}',
                client_id: '{{ $quote->client_id }}'
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

        $('.btn-save-quote').click(function () {
            var items = [];
            var display_order = 1;
            var custom_fields = {};
            var apply_exchange_rate = $(this).data('apply-exchange-rate');
            var files = [];
            var quote_data = {};
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
                var fieldName = $(this).data('quotes-field-name');
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

                    custom_fields[$(this).data('quotes-field-name')] = $(this).val();
                }
            });

            quote_data['number'] = $('#number').val();
            quote_data['quote_date'] = $('#quote_date').val();
            quote_data['expires_at'] = $('#expires_at').val();
            quote_data['status'] = $('#status').val();
            quote_data['items'] = items;
            quote_data['terms'] = $('#terms').val();
            quote_data['footer'] = $('#footer').val();
            quote_data['currency_code'] = $('#currency_code').val();
            quote_data['exchange_rate'] = $('#exchange_rate').val();
            quote_data['custom'] = custom_fields;
            quote_data['apply_exchange_rate'] = typeof apply_exchange_rate === 'undefined' ? '' : apply_exchange_rate;
            quote_data['template'] = $('#template').val();
            quote_data['summary'] = $('#summary').val();
            quote_data['discount'] = $('#discount').val();
            quote_data['custom_files'] = files;

            form_data = objectToFormData(quote_data);
            $.ajax({
                url: '{{ route('quotes.update', [$quote->id]) }}',
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
                $('#div-quote-edit').load('{{ route('quoteEdit.refreshEdit', [$quote->id]) }}', function () {
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
            var url = "{{ route('quoteEdit.deleteImage', [$quote->id,'field_name' => '']) }}";
            $.post(url + '/' + $(this).data('field-name')).done(function () {
                $('.custom_img').html('');
            });
        });

        alertify.defaults.theme.ok = "ui negative button";
        alertify.defaults.theme.cancel = "ui black button";
        $("<style>").text(".ajs-header{ background-color: #ba0606 !important; }").appendTo($("body"));

        $(document).on('click', '.btn-delete-quote-item', function () {
            var id = $(this).data('item-id');
            if (typeof id === 'undefined') {
                $(this).closest('tr').remove();
            } else {
                alertify.confirm('{{ trans('fi.delete_record_warning') }}', function () {
                    $.post('{{ route('quoteItem.delete') }}', {
                        id: id
                    }).done(function () {
                        $('#tr-item-' + id).remove();
                        $('#div-totals').load('{{ route('quoteEdit.refreshTotals') }}', {
                            id: '{{ $quote->id }}'
                        });
                    });
                }, function () {
                    alertify.alert().destroy();
                }).setHeader(confirmHeader).set({transition: 'zoom', defaultFocus: 'cancel'});
            }

        });

        $('.btn-delete-quote').click(function () {
            alertify.confirm("{!! trans('fi.delete_record_warning') !!}", function () {
                $.ajax({
                    url: '{{ route('quotes.delete', [$quote->id]) }}',
                    method: 'get',
                    beforeSend: function () {
                        $(".modal-loader").show();
                    },
                    success: function () {
                        alertify.success('{{ trans('fi.record_successfully_deleted') }}', 5);
                        window.location.replace('{{ route('quotes.index') }}');
                    }
                });
            }, function () {
                alertify.alert().destroy();
            }).setHeader(confirmHeader).set({transition: 'zoom', defaultFocus: 'cancel'});

        });

        $('#btn-print-quote').click(function () {
            $.get($(this).data('action')).done(function (response) {
                window.open(response).print();
            });
        });

    });

</script>