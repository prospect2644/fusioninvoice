<script type="text/javascript">
    $(function () {

        alertify.defaults.theme.ok = "ui negative button";
        alertify.defaults.theme.cancel = "ui black button";

        $("<style>").text(".ajs-header{ background-color: #ba0606 !important; }").appendTo($("body"));

        $('.invoice_filter_options').change(function () {
            $('form#filter').submit();
        });
        $('#tags-filter-open').click(function () {
            $('#modal-placeholder').load('{!! route('invoice.filterTags', ['tags' => json_encode($tags), 'tagsMustMatchAll' => $tagsMustMatchAll, 'firstLoad' => true]) !!}')
        });
        $('#btn-bulk-delete').click(function () {

            var ids = [];

            $('.bulk-record:checked').each(function () {
                ids.push($(this).data('id'));
            });

            if (ids.length > 0) {

                alertify.confirm("{!! trans('fi.bulk_delete_record_warning') !!}", function () {
                    $.ajax({
                        url: "{{ route('invoices.bulk.delete') }}",
                        method: 'post',
                        data: {ids: ids},
                        beforeSend: function () {
                            $(".modal-loader").show();
                        },
                        success: function () {
                            $(".modal-loader").hide();
                            window.location = decodeURIComponent("{{ urlencode(request()->fullUrl()) }}");
                        }
                    });
                }, function () {
                    alertify.alert().destroy();
                }).setHeader(confirmHeader).set({transition: 'zoom', defaultFocus: 'cancel'});

            }
        });

        $('.bulk-change-status').click(function () {
            var ids = [];
            var status = $(this).data('status');

            $('.bulk-record:checked').each(function () {
                ids.push($(this).data('id'));
            });

            if (ids.length > 0) {

                alertify.confirm("{!! trans('fi.bulk_invoice_change_status_warning') !!}", function () {
                    $.post("{{ route('invoices.bulk.status') }}", {
                        ids: ids,
                        status: status
                    }).done(function () {
                        window.location = decodeURIComponent("{{ urlencode(request()->fullUrl()) }}");
                    });
                }, function () {
                    alertify.alert().destroy();
                }).setHeader(confirmHeader).set({transition: 'zoom', defaultFocus: 'cancel'});

            }
        });

        $('#btn-bulk-pdf').click(function () {

            var ids = [];
            $('.bulk-record:checked').each(function () {
                ids.push($(this).data('id'));
            });
            if (ids.length > 0) {
                window.location = "{{ route('invoices.bulk.pdf') }}?ids=" + ids.join()
            }
        });

        $('#btn-bulk-print').click(function () {

            var ids = [];
            $('.bulk-record:checked').each(function () {
                ids.push($(this).data('id'));
            });
            if (ids.length > 0) {
                $(".modal-loader").show();
                $.get("{{ route('invoices.bulk.save.pdf') }}?ids=" + ids.join()).done(function (response) {
                    $(".modal-loader").hide();
                    window.open(response).print();
                });
            }
        });

        $('.delete-invoice').click(function () {

            var $_this = $(this);

            alertify.confirm("{!! trans('fi.delete_record_warning') !!}", function () {
                $.ajax({
                    url: $_this.data('action'),
                    method: 'get',
                    beforeSend: function () {
                        $(".modal-loader").show();
                    },
                    success: function (data) {
                        $(".modal-loader").hide();
                        if (data.error) {
                            alertify.error(data.error, 5);
                        } else {
                            $_this.closest('tr').remove();
                            alertify.success('{{ trans('fi.record_successfully_deleted') }}', 5);
                        }
                    }
                });
            }, function () {
                alertify.alert().destroy();
            }).setHeader(confirmHeader).set({transition: 'zoom', defaultFocus: 'cancel'});

        });

        $('.apply-to-invoices').click(function () {
            var url = '{{ route("payments.prepareCreditApplication", ":creditMemo") }}';
            url = url.replace(':creditMemo', $(this).data('invoice-id'));
            var redirect_url = $(this).data('redirect-to');
            $('#modal-placeholder').load(url, {
                redirect_to: redirect_url
            });
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

        $('#btn-clear-filters').click(function () {
            $('#search').val('');
            $('#tags-filter').val('');
            $('#tags-must-match-all').val(0);
            $('.invoice_filter_options').prop('selectedIndex', 0);
            $('#filter').submit();
        });

        $('.btn-copy-invoice').click(function () {
            $('#modal-placeholder').load('{{ route('invoiceCopy.create') }}', {
                invoice_id: $(this).data('invoice-id')
            });
        });

        $('.btn-print-invoice').click(function () {
            $(".modal-loader").show();
            $.get($(this).data('action')).done(function (response) {
                $(".modal-loader").hide();
                window.open(response).print();
            });
        });
    });
</script>