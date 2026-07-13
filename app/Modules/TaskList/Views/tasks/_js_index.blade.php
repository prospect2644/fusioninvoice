@include('layouts._daterangepicker')

<script type="text/javascript">
    $(function () {

        alertify.defaults.theme.ok = "ui negative button";
        alertify.defaults.theme.cancel = "ui black button";

        $("<style>").text(".ajs-header{ background-color: #ba0606 !important; }").appendTo($("body"));

        $('.task-status').change(function () {
            $('form#filter').submit();
        });

        $('#date_range_filter').daterangepicker({
            autoApply: true,
            autoUpdateInput: false
        }, function (start, end) {
            let from = start.format('{{ strtoupper(config('fi.datepickerFormat')) }}');
            let to = end.format('{{ strtoupper(config('fi.datepickerFormat')) }}');

            $(this.element).val(from + '-' + to);
            $('#date_range_filter_from').val(from);
            $('#date_range_filter_to').val(to);
        });

        $('#date_range_filter').on('apply.daterangepicker', function (ev, picker) {
            $('form#filter').submit();
        });

        $('.action-complete').on('click', function () {
            let returnURL = document.URL;
            let task_id = $(this).data('task-id');
            var url = '{{ route("task.complete",[ ":id", ":complete"] ) }}';
            url = url.replace(':id', task_id);
            url = url.replace(':complete', 1);
            var tab = $(this).data('tab');

            $.post(url).done(function () {
                if (tab) {
                    var url = new URL(returnURL);
                    url.searchParams.set("tab", tab);
                    window.location.replace(url.href);
                } else {
                    window.location.replace(returnURL);
                }
            });
        });

        $('.action-reopen').on('click', function () {
            let returnURL = document.URL;
            let task_id = $(this).data('task-id');
            var url = '{{ route("task.complete",[ ":id", ":complete"] ) }}';
            url = url.replace(':id', task_id);
            url = url.replace(':complete', 0);
            var tab = $(this).data('tab');

            $.post(url).done(function () {
                if (tab) {
                    var url = new URL(returnURL);
                    url.searchParams.set("tab", tab);
                    window.location.replace(url.href);
                } else {
                    window.location.replace(returnURL);
                }
            });
        });

        $('.action-delete').on('click', function () {
            let returnURL = document.URL;
            let url = $(this).data('action');
            var tab = $(this).data('tab');

            alertify.confirm("{!! trans('fi.delete_record_warning') !!}", function () {
                $.get(url).done(function () {
                    if (tab) {
                        var url = new URL(returnURL);
                        url.searchParams.set("tab", tab);
                        window.location.replace(url.href);
                    } else {
                        window.location.replace(returnURL);
                    }
                });
            }, function () {
                alertify.alert().destroy();
            }).setHeader(confirmHeader).set({transition: 'zoom', defaultFocus: 'cancel'});
        });

        $('#btn-clear-filters').click(function () {
            $('#search').val('');
            $('#date_range_filter').val('');
            $('#date_range_filter_from').val('');
            $('#date_range_filter_to').val('');
            $('.task-status').prop('selectedIndex', 0);
            $('#filter').submit();
        });

    });
</script>