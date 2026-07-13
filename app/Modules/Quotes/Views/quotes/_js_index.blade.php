<script type="text/javascript">
    $(function () {
        alertify.defaults.theme.ok = "ui negative button";
        alertify.defaults.theme.cancel = "ui black button";

        $("<style>").text(".ajs-header{ background-color: #ba0606 !important; }").appendTo($("body"));

        $('.quote_filter_options').change(function () {
            $('form#filter').submit();
        });

        $('#btn-bulk-delete').click(function () {
            var ids = [];

            $('.bulk-record:checked').each(function () {
                ids.push($(this).data('id'));
            });

            if (ids.length > 0) {

                alertify.confirm("{!! trans('fi.bulk_delete_record_warning') !!}", function () {
                    $.ajax({
                        url: "{{ route('quotes.bulk.delete') }}",
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

        $('#btn-bulk-print').click(function () {

            var ids = [];
            $('.bulk-record:checked').each(function () {
                ids.push($(this).data('id'));
            });
            if (ids.length > 0) {
                $(".modal-loader").show();
                $.get("{{ route('quotes.bulk.save.pdf') }}?ids=" + ids.join()).done(function (response) {
                    $(".modal-loader").hide();
                    window.open(response).print();
                });
            }
        });

        $('.bulk-change-status').click(function () {
            var ids = [];
            var status = $(this).data('status');

            $('.bulk-record:checked').each(function () {
                ids.push($(this).data('id'));
            });

            if (ids.length > 0) {

                alertify.confirm("{!! trans('fi.bulk_quote_change_status_warning') !!}", function () {
                    $.post("{{ route('quotes.bulk.status') }}", {
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

        $('.delete-quote').click(function () {

            var $_this = $(this);

            alertify.confirm("{!! trans('fi.delete_record_warning') !!}", function () {
                $.ajax({
                    url: $_this.data('action'),
                    method: 'get',
                    beforeSend: function () {
                        $(".modal-loader").show();
                    },
                    success: function () {
                        $_this.closest('tr').remove();
                        $(".modal-loader").hide();
                        alertify.success('{{ trans('fi.record_successfully_deleted') }}', 5);
                    }
                });
            }, function () {
                alertify.alert().destroy();
            }).setHeader(confirmHeader).set({transition: 'zoom', defaultFocus: 'cancel'});

        });
        $('#btn-bulk-pdf').click(function () {

            var ids = [];
            $('.bulk-record:checked').each(function () {
                ids.push($(this).data('id'));
            });
            if (ids.length > 0) {
                window.location = "{{ route('quotes.bulk.pdf') }}?ids=" + ids.join()
            }
        });
        $('#btn-clear-filters').click(function () {
            $('#search').val('');
            $('.quote_filter_options').prop('selectedIndex', 0);
            $('#filter').submit();
        });

        $('.btn-print-quote').click(function () {
            $(".modal-loader").show();
            $.get($(this).data('action')).done(function (response) {
                $(".modal-loader").hide();
                window.open(response).print();
            });
        });
    });
</script>