<script type="text/javascript">
    $.AdminLTE.options.boxWidgetOptions.boxWidgetIcons.collapse = 'fa-caret-down task-section-header';
    $.AdminLTE.options.boxWidgetOptions.boxWidgetIcons.open = 'fa-caret-right task-section-header';
    $.AdminLTE.boxWidget.activate();

    function initSorting() {
        $('.task-section-box tr').mouseover(function () {
            $(this).find('.task-action-btn').removeClass('hide').addClass('inline');
            $(this).find('.movericon').css('opacity', 100);

        }).mouseout(function () {
            $(this).find('.task-action-btn').removeClass('inline').addClass('hide');
            $(this).find('.movericon').css('opacity', 0);
        });

        $('.task-section-box-header').mouseover(function () {
            $(this).find('.btn-add-task-to-section, .btn-sort-section').removeClass('hide').addClass('inline');
        }).mouseout(function () {
            if (!$(this).siblings('.box-body').find('.add-task-to-section-form').is(':visible')) {
                $(this).find('.btn-add-task-to-section, .btn-sort-section').removeClass('inline').addClass('hide');
            }
        });

        $(".task-section-list-table tbody").sortable({
            helper: fixHelper,
            connectWith: '.ui-sortable',
            update: function () {
                var Lists = $(this).find('.order-id');
                var reOrder = [];

                $.each(Lists, function (key, value) {
                    reOrder.push($(value).val());
                });

                reOrder.length == 0 ? $(this).css('display', 'block') : '';

                if (reOrder.length > 0) {
                    $(this).css('display', '');
                    var sectionId = $(this).parent('table').prev().find('.task_section_id').val();
                    var form_data = objectToFormData({ids: reOrder, task_section_id: sectionId});
                    $.ajax({
                        url: '{{ route('task.widget.reorder') }}',
                        method: 'post',
                        data: form_data,
                        processData: false,
                        contentType: false
                    }).fail(function (response) {
                        $.each($.parseJSON(response.responseText).errors, function (id, message) {
                            alertify.error(message[0], 5);
                        });
                    });
                }

            }
        });
    }


    $('.btn-add-task-to-section').click(function () {
        $(this).closest('.box-header').siblings('.box-body').find('.add-task-to-section-form').removeClass('hide').addClass('show');
        $(this).closest('.box-header').siblings('.box-body').find("[name='title']").focus();
    });

    $('.btn-add-task-to-section-cancel').click(function () {
        $(this).closest('.add-task-to-section-form').removeClass('show').addClass('hide');
        $(this).closest('.task-section-box').find('.btn-add-task-to-section').removeClass('show').addClass('hide');
    });

    $('.select2-select-box').select2();

    @if(config('fi.includeTimeInTaskDueDate') == 1)
        $('.add-task-to-section-due-date').datetimepicker({defaultDate: new Date(), date: new Date()});
    @else
        $('.add-task-to-section-due-date').datepicker({
                format: '{{ config('fi.datepickerFormat') }}',
                autoclose: true,
                startDate: new Date(),
                date: new Date(),
                todayHighlight: true
            }).datepicker("setDate", 'now');
    @endif
    $('.fa-calendar').click(function () {
                $('.add-task-to-section-due-date').focus();
            });
    $('.btn-add-task-to-section-submit').click(function (e) {
        e.preventDefault();
        let $this = $(this);
        let $form = $this.closest('form');
                @if(config('fi.includeTimeInTaskDueDate') == 1)
                    var date = $form.find('.add-task-to-section-due-date').data("DateTimePicker").date();
                @else
                    var date = $form.find('.add-task-to-section-due-date').datepicker("getDate");
        @endif
        if (date) {
            $form.find("input[name='due_date_timestamp']").val(moment(date).format('YYYY-MM-DD HH:mm:ss'));
        }
        else {
            $form.find("input[name='due_date_timestamp']").val('');
        }

        $.post($form.attr('action'), $form.serializeArray(), function (response) {
            alertify.success(response.message, 1);
            $this.closest('.add-task-to-section-form').removeClass('show').addClass('hide');
            $('#reload-task').trigger('click');
        }).fail(function (xhr) {
            let errors = JSON.parse(xhr.responseText).errors;
            $.each(errors, function (name, data) {
                alertify.error(data[0], 5);
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


    $(document).on('click', ".btn-sort-section", function () {
        let $this = $(this);
        let sectionId = $this.data('section-id');
        let dir = $(this).attr('data-dir');
        $('.btn-sort-section').attr('data-dir', dir == 'asc' ? 'desc' : 'asc');
        $.ajax({
            url: '{{ route('task.widget.sort') }}',
            method: 'post',
            data: {sectionId: sectionId, dir: dir},
            beforeSend: function () {
                $(".modal-loader").show();
            },
            success: function (response) {
                $('.task-section-' + sectionId).html(response);
                $(".modal-loader").hide();
                initSorting();
            }
        });

    });

    initSorting();
</script>

@if($tasks)
    <div class="row">

        <div class="col-xs-12" style="padding-left:10px;padding-right:10px;">

            @foreach($tasks as $section => $sectionData)
                @if($section == 0 && count($sectionData['tasks']) == 0)
                @else
                    <div class="box border-top-none margin-bottom-0 task-section-box">
                        <div class="box-header with-border task-section-box-header">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse">
                                <i class="fa fa-caret-down task-section-header"></i>

                                <h3 class="box-title" style="color: black;">{{ $sectionData['sectionName'] }}</h3>
                            </button>
                            <div class="pull-right">
                                <button type="button" title="{{ trans('fi.sort_by_due') }}" data-dir="asc"
                                        class="btn btn-box-tool hide btn-sort-section btn-box-tool-background"
                                        style="color: black" ;
                                        data-section-id="{{ $sectionData['sectionId'] }}">
                                    <i class="fa fa-clock-o"></i>
                                </button>
                                <button type="button" title="{{ trans('fi.add_item') }}"
                                        class="btn btn-box-tool hide btn-add-task-to-section btn-box-tool-background"
                                        style="color: black" ;
                                        data-section-id="{{ $sectionData['sectionId'] }}">
                                    <i class="fa fa-plus"></i>
                                    {{ trans('fi.add_item') }}
                                </button>
                            </div>
                        </div>
                        <div class="box-body">
                            <div class="add-task-to-section-form hide">
                                {!! Form::open(['route' => 'task.widget.store', 'method' => 'post', 'role' => 'form', 'id' => 'task-add-to-' . $sectionData['sectionName'] . '-form', 'files' => true]) !!}
                                <table class="table table-striped table-responsive margin-bottom-0"
                                       style="table-layout: fixed !important;">
                                    <tr>
                                        <td width="16%">
                                            {!! Form::select('assignee_id', $users, auth()->user()->id, ['class' => 'form-control select2-select-box input-sm', 'placeholder' => trans('fi.assignee'), 'style'=> 'width:100%']) !!}
                                        </td>
                                        <td width="51%">
                                            {!! Form::text('title', null, ['class' => 'form-control input-sm', 'placeholder' => trans('fi.title'), 'autocomplete' => 'off']) !!}
                                        </td>
                                        <td width="22%">
                                            <div class="input-group date">
                                                {!! Form::text('due_date', null, ['class' => 'form-control input-sm add-task-to-section-due-date', 'placeholder' => trans('fi.due_date')]) !!}
                                                <div class="input-group-addon">
                                                    <i class="fa fa-calendar"></i>
                                                </div>
                                            </div>
                                            {!! Form::hidden('due_date_timestamp', null, ['class' => 'due_date_timestamp']) !!}
                                        </td>
                                        <td width="11%" style="vertical-align: middle; text-align: end;">
                                            <button class="btn btn-xs btn-danger btn-add-task-to-section-cancel"
                                                    type="button">
                                                <i class="fa fa-trash" title="{{ trans('fi.cancel') }}"></i>
                                            </button>
                                            <button class="btn btn-xs btn-primary btn-add-task-to-section-submit"
                                                    type="button">
                                                <i class="fa fa-save" title="{{ trans('fi.save') }}"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </table>
                                {!! Form::hidden('task_section_id', $sectionData['sectionId'], ['class' => 'task_section_id form-control']) !!}
                                {!! Form::hidden('description', '', ['class' => 'form-control']) !!}
                                {!! Form::close() !!}
                            </div>
                            <table class="table table-striped table-responsive margin-bottom-0 task-section-list-table task-section-{{ $sectionData['sectionId'] }}">
                                <tbody style="{{ count($sectionData['tasks']) == 0 ? 'display:block' : '' }}">
                                @foreach($sectionData['tasks'] as $task)
                                    <tr class="cursor-move">
                                        <td style="text-align: center; vertical-align: middle;height: 60px;" width="3%">
                                            <i class="fa fa-sort movericon" style="opacity: 0;"></i>
                                            <input type="hidden" value="{{ $task->id }}" class="order-id">
                                        </td>
                                        <td style="text-align: center; vertical-align: middle;height: 60px;" width="9%">
                                            <input type="checkbox" id="task_status_{{ $task->id }}" class="task-status"
                                                   style="display: none;" {{ $task->is_complete ? ' checked' : '' }}
                                                   data-link="{{ route('task.complete', ['id' => $task->id, 'complete' => $task->is_complete ? '0' : '1']) }}">
                                            <label for="task_status_{{ $task->id }}" class="check">
                                                <svg width="18px" height="18px" viewBox="0 0 18 18">
                                                    <title> {{ trans('fi.done') }}</title>
                                                    <path d="M1,9 L1,3.5 C1,2 2,1 3.5,1 L14.5,1 C16,1 17,2 17,3.5 L17,14.5 C17,16 16,17 14.5,17 L3.5,17 C2,17 1,16 1,14.5 L1,9 Z"></path>
                                                    <polyline points="1 9 7 14 15 4"></polyline>
                                                </svg>
                                            </label>
                                        </td>
                                        <td width="7%" style="vertical-align: middle; padding-top: 5px;">
                                            <i class="fa initials">
                                                {!! $task->assignee_id ? $task->assignee->getAvatar(26) : '' !!}
                                            </i>
                                        </td>
                                        <td width="66%" class="word-wrap-all" style="padding-top: 1.03em;">
                                            <span class="{{ $task->is_complete ? 'strikethrough' : ''}}"> {{ $task->title }} </span>

                                            @if($task->attachments->count() > 0 || $task->notes->count() > 0 || $task->client || $task->due_date)
                                                <p style="text-align: end; margin: 5px 0 -5px 0">
                                                    @if($task->attachments->count() > 0)
                                                        <span class="label label-default"><i
                                                                    class="fa fa-paperclip"> </i> {{ $task->attachments->count() }}</span>
                                                    @endif
                                                    @if($task->notes->count() > 0)
                                                        <span class="label label-default"><i
                                                                    class="fa fa-comments-o"> </i> {{ $task->notes->count() }}</span>
                                                    @endif
                                                    @if($task->client)
                                                        <span class="task-list-smaller-font"
                                                              style="padding-left: 15px;"> {!! $task->client ? '<a href="'.route('clients.show',$task->client).'">'.$task->client->name.'</a>' : '' !!}</span>
                                                    @endif
                                                    @if($task->due_date)
                                                        <span class="task-list-smaller-font {!! ($task->overdue && !$task->is_complete ? 'text-danger' : ($task->dueToday && !$task->is_complete ? 'text-success' : 'task-not-due'))!!}"
                                                              style="padding-left: 15px;"> {{ $task->formatted_due_date }} </span>
                                                    @endif
                                                </p>
                                            @endif
                                        </td>

                                        <td style="text-align:right;" width="15%">

                                            @if(!$task->is_complete)
                                                <button class="btn btn-xs btn-edit-task task-action-btn task-edit-btn hide"
                                                        data-link="{{ route('task.widget.edit', ['id' => $task->id]) }}">
                                                    <i class="fa fa-edit" title="{{ trans('fi.edit') }}"></i>
                                                </button>
                                            @endif

                                            <button class="btn btn-xs btn-delete-task task-action-btn task-delete-btn hide"
                                                    data-action="{{ route('task.delete', ['id' => $task->id]) }}">
                                                <i class="fa fa-trash" title="{{ trans('fi.delete') }}"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>

        <div class="pull-left">
            <div id="tasks-filter">
                @if(request('search') || (request('status') && request('status') != 'open'))
                    <button type="button" class="btn btn-link" id="btn-clear-filters">{{ trans('fi.clear') }}</button>
                @endif
            </div>
        </div>
    </div>
@endif