<script type="text/javascript">

    $(function () {

        alertify.defaults.theme.ok = "ui negative button";
        alertify.defaults.theme.cancel = "ui black button";

        $("<style>").text(".ajs-header{ background-color: #ba0606 !important; }").appendTo($("body"));

        @can('payments.delete')
        $('#btn-bulk-delete').click(function () {

                    var ids = [];

                    $('.bulk-record:checked').each(function () {
                        ids.push($(this).data('id'));
                    });

                    if (ids.length > 0) {
                        alertify.confirm("{!! trans('fi.delete_record_warning') !!}", function () {
                            $.ajax({
                                url: "{{ route('payments.bulk.delete') }}",
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

        $('.delete-payment').click(function () {

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
        @endcan

        $('#btn-clear-filters').click(function () {
                    $('#search').val('');
                    $('#filter').submit();
                });

    });

</script>