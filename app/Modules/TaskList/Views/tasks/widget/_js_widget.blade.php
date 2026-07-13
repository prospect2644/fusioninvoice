@include('layouts._daterangepicker')
<script type="text/javascript">
    $(function () {

        alertify.defaults.theme.ok = "ui negative button";
        alertify.defaults.theme.cancel = "ui black button";

        $("<style>").text(".ajs-header{ background-color: #ba0606 !important; }").appendTo($("body"));

        let populateTaskList = function (link) {
            $('#task-list-container').load(link);
        };
        let $body = $('body');

        $('#create-new-task').click(function () {
            $('#modal-placeholder').load('{{ route('task.widget.create') }}');
        });

        $body.on('change', '.task-status', function () {
            let $this = $(this);
            let id = $this.attr('id').replace('_edit', '');
            let $completePost = $.post($this.attr('data-link'));
            $completePost.done(function () {
                let $_this = $('#' + id);
                if ($this.is(':checked')) {
                    $_this.prop("checked", true);
                } else {
                    $_this.prop("checked", false);
                }

                let oldLink = $_this.data('link').slice(0, -1);
                let status = $_this.is(':checked') ? 0 : 1;
                $_this.attr('data-link', oldLink + status);

                if ($_this.is(':checked')) {
                    $_this.closest('tr').find('.btn-edit-task').addClass('disabled').hide();
                    $_this.closest('tr').find('td:eq(3)').find('span').addClass('strikethrough');
                } else {
                    $_this.closest('tr').find('td:eq(0)').addClass($_this.closest('tr').data('class'));
                    $_this.closest('tr').find('.btn-edit-task').removeClass('disabled').show();
                    $_this.closest('tr').find('td:eq(3)').find('span').removeClass('strikethrough');
                }
            });
            $completePost.fail(function (xhr, status, error) {
                alertify.error(error, 5);
            });
        }).on('click', '.btn-edit-task', function () {
            if (!$(this).hasClass('disabled')) {
                $('#modal-placeholder').load($(this).data('link'));
            }
        }).on('click', '.sortable-task-list-header a', function (e) {
            e.preventDefault();
            populateTaskList($(this).attr('href'));
        });


        $body.on('click', '#btn-clear-filters', function () {
            $('#search, #date_range_filter, #date_range_filter_from, #date_range_filter_to').val('');
            $('#task-list-filter').prop('selectedIndex', 0);
            $('#task-filter').prop('selectedIndex', 0);
            $("#tasks-filter-form").submit();
        });

        $body.on('click', '#reload-task', function () {
            $('.reload-task').addClass('fa-spin')
            setTimeout(function () {
                $('.reload-task').removeClass('fa-spin')
            }, 1500);
            $.ajax({
                url: '{{ route('task.widget.refresh') }}',
                method: 'post',
                beforeSend: function () {
                    $(".modal-loader").show();
                },
                success: function () {
                    $(".modal-loader").hide();
                    $('#search, #date_range_filter, #date_range_filter_from, #date_range_filter_to').val('');
                    $('#task-list-filter').prop('selectedIndex', 0);
                    $('#task-filter').prop('selectedIndex', 0);
                    $("#tasks-filter-form").submit();
                }
            });
        });

        $body.on('click', '.btn-delete-task', function () {
            var $_this = $(this);

            alertify.confirm("{!! trans('fi.delete_record_warning') !!}", function () {
                $.ajax({
                    url: $_this.data('action'),
                    method: 'get',
                    beforeSend: function () {
                        $(".modal-loader").show();
                    },
                    success: function () {
                        $(".modal-loader").hide();
                        $('#search-btn').trigger('click');
                    }
                });
            }, function () {
                alertify.alert().destroy();
            }).setHeader(confirmHeader).set({transition: 'zoom', defaultFocus: 'cancel'});

        });

        $('.custom-search').click(function () {
            $("#tasks-filter-form").submit();
            $('#modal-search-config').modal('hide');
        });

        $('.close-search-config-modal').click(function () {
            $('#modal-search-config').modal('hide');
        });

        $('#task-list-filter,#task-filter').change(function () {
            $("#tasks-filter-form").submit();
        });

        $('.search-config-chk').change(function () {
            let checked = false;
            $.each($(".search-config-chk:checked"), function () {
                checked = true;
            });

            if (checked == false) {
                $('#search-config-btn').addClass('btn-danger').closest('.input-group').addClass('has-error');
            } else {
                $('#search-config-btn').removeClass('btn-danger').closest('.input-group').removeClass('has-error');
            }
        });

        $('#tasks-filter-form').submit(function (e) {
            e.preventDefault();
            let $form = $(this);
            let url = $form.attr('action');
            let data = $form.serializeArray();
            let checked = false;
            $.each($(".search-config-chk:checked"), function () {
                checked = true;
            });
            if (checked == true) {
                $.get(url, data, function (response) {
                    $('#task-list-container').html(response);
                });
            }
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

        $('#date_range_filter').on('apply.daterangepicker', function () {
            $('#tasks-filter-form').submit();
        });

        $('#search-btn').trigger('click');
    });
</script>