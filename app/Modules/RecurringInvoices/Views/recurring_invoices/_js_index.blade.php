<script type="text/javascript">
    $(function () {
        $('.recurring_invoice_filter_options').change(function () {
            $('form#filter').submit();
        });
        $('#tags-filter-open').click(function () {
            $('#modal-placeholder').load('{!! route('recurringInvoice.filterTags', ['tags' => json_encode($tags), 'tagsMustMatchAll' => $tagsMustMatchAll, 'firstLoad' => true]) !!}')
        });
        alertify.defaults.theme.ok = "ui negative button";
        alertify.defaults.theme.cancel = "ui black button";

        $("<style>").text(".ajs-header{ background-color: #ba0606 !important; }").appendTo($("body"));

        $('.delete-recurring-invoice').click(function () {

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

        $('#btn-clear-filters').click(function () {
            $('#search').val('');
            $('#tags-filter').val('');
            $('#tags-must-match-all').val(0);
            $('.recurring_invoice_filter_options').prop('selectedIndex', 0);
            $('#filter').submit();
        });
    });
</script>