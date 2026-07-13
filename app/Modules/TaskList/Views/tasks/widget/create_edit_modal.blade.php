@include('layouts._datepicker')
@include('layouts._datetimepicker')
@include('layouts._select2')
<script src='{{ asset('assets/plugins/daterangepicker/moment.js') }}'></script>
<script type="text/javascript">
    $(function () {
        // Setup the ui
        $('#task-modal').modal('show');

        $("#task-modal").on('shown.bs.modal', function () {
            $('#title').focus();
        });
        @if(config('fi.includeTimeInTaskDueDate') == 1)
            @if($editMode)
                    $('#task-due-date').datetimepicker();
        @else
            $('#task-due-date').datetimepicker({defaultDate: new Date(), date: new Date()});
        @endif
    @else
        @if($editMode)
        $('#task-due-date').datepicker({
                    format: '{{ config('fi.datepickerFormat') }}',
                    autoclose: true,
                    todayHighlight: true
                });
        @else
        $('#task-due-date').datepicker({
                    format: '{{ config('fi.datepickerFormat') }}',
                    autoclose: true,
                    startDate: new Date(),
                    date: new Date(),
                    todayHighlight: true
                }).datepicker("setDate", 'now');
        @endif
    @endif
    $('.fa-calendar').click(function () {
                    $('#task-due-date').focus();
                });
        $('.select2-select-box').select2();

        // Submit the form
        let formData = new FormData();
        $('#task-save-confirm').click(function () {
            let $form = $('#task-form');
                    @if(!$editMode && !config('app.demo'))
                        @can('attachments.create')
                                let attachments = document.getElementById('attachments').files.length;
            for (var i = 0; i < attachments; i++) {
                formData.append("attachments[]", document.getElementById('attachments').files[i]);
            }
            @endcan;
                    @endif
                        @if(config('fi.includeTimeInTaskDueDate') == 1)
                            var date = $('#task-due-date').data("DateTimePicker").date();
                    @else
                        var date = $('#task-due-date').datepicker("getDate");
            @endif
            formData.append("due_date_timestamp", moment(date).format('YYYY-MM-DD HH:mm:ss'));
            formData.append("due_date", $('#task-due-date').val());
            formData.append("title", $('#title').val());
            formData.append("description", $('#description').val());
            formData.append("assignee_id", $('#task-assignee-select').val());
            formData.append("client_id", $('#task-client-select').val());
            formData.append("task_section_id", $('#task-section-select').val());

            $.ajax({
                url: $form.attr('action'),
                method: 'post',
                data: formData,
                processData: false,
                contentType: false
            }).done(function (response) {
                alertify.success(response.message, 5);
                $('#task-modal').modal('hide');
                $('#search-btn').trigger('click');
            }).fail(function (xhr, status, error) {
                let errors = JSON.parse(xhr.responseText).errors;
                $.each(errors, function (name, data) {
                    alertify.error(data[0], 5);
                });
            });

        });

        $('#task-assignee-select').change(function () {
            let $userId = '{{ auth()->user()->id }}';
            if ($(this).val() != $userId) {
                $("#task-section-select").val(1).change().attr('readonly', true);
            } else {
                $("#task-section-select").val(2).change().attr('readonly', false);
            }
        });
    });
</script>
<div class="modal fade" id="task-modal" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">{{ trans('fi.task') }}</h4>
            </div>
            <div class="modal-body" style="max-height: calc(100vh - 200px);overflow-y: auto;">
                @if($editMode)
                    <div class="row">
                        <div class="col-md-12">
                            <div class="col-md-10 text-right">
                                <label>{{ trans('fi.complete') }}</label>
                            </div>
                            <div class="col-md-2">
                                <input type="checkbox" id="task_status_edit_{{ $task->id }}" class="task-status"
                                       style="display: none;" {{ $task->is_complete ? ' checked' : '' }}
                                       data-link="{{ route('task.complete', ['id' => $task->id, 'complete' => $task->is_complete ? '0' : '1']) }}">
                                <label for="task_status_edit_{{ $task->id }}" class="check">
                                    <svg width="18px" height="18px" viewBox="0 0 18 18">
                                        <path d="M1,9 L1,3.5 C1,2 2,1 3.5,1 L14.5,1 C16,1 17,2 17,3.5 L17,14.5 C17,16 16,17 14.5,17 L3.5,17 C2,17 1,16 1,14.5 L1,9 Z"></path>
                                        <polyline points="1 9 7 14 15 4"></polyline>
                                    </svg>
                                </label>
                            </div>
                        </div>
                    </div>
                @endif
                <div class="row">
                    <div class="col-md-12">
                        {!! Form::open(['route' => ($editMode) ? ['task.widget.update', $task->id] : 'task.widget.store', 'method' => 'post', 'role' => 'form', 'id' => 'task-form', 'files' => true]) !!}
                        <div class="form-group col-md-12">
                            <label>{{ trans('fi.title') }}</label>
                            {!! Form::text('title', $task->title ?? null, ['id' => 'title', 'class' => 'form-control input-sm', 'placeholder' => trans('fi.title'), 'autocomplete' => 'off']) !!}
                        </div>
                        <div class="form-group col-md-12">
                            <label>{{ trans('fi.description') }}</label>
                            {!! Form::textarea('description', $task->description ?? null, ['id' => 'description', 'class' => 'form-control input-sm', 'placeholder' => trans('fi.description'), 'rows' => 3]) !!}
                        </div>
                        <div class="form-group col-md-6">
                            <label>{{ trans('fi.due_date') }}</label>

                            <div class="input-group date">
                                {!! Form::text('due_date', $task->due_date_epoch ?? null, ['class' => 'form-control input-sm', 'id' => 'task-due-date', 'placeholder' => trans('fi.due_date')]) !!}
                                <div class="input-group-addon btn-sm">
                                    <i class="fa fa-calendar"></i>
                                </div>
                            </div>
                        </div>
                        <div class="form-group col-md-6">
                            <label>{{ trans('fi.task_section') }}</label>
                            {!! Form::select('task_section_id', $taskSections, isset($task) ? $task->task_section_id : 2, ['class' => 'form-control input-sm', 'id' => 'task-section-select', 'placeholder' => trans('fi.select_section')]) !!}
                        </div>
                        <div class="form-group select2-form-control col-md-6">
                            <label>{{ trans('fi.assignee') }}</label>
                            {!! Form::select('assignee_id', $users, isset($task) ? $task->assignee_id : auth()->user()->id, ['class' => 'form-control select2-select-box', 'id' => 'task-assignee-select']) !!}
                        </div>
                        <div class="form-group select2-form-control col-md-6">
                            <label>{{ trans('fi.client') }}</label>
                            {!! Form::select('client_id', $clients, isset($task) ? $task->client_id : $client, ['class' => 'form-control select2-select-box', 'id' => 'task-client-select', 'placeholder' => trans('fi.select_client')]) !!}
                        </div>

                        @if (!$editMode)
                            @if (!config('app.demo'))
                                @can('attachments.create')
                                <div class="form-group col-md-12">
                                    <label>{{ trans('fi.attach_files') }}: </label>
                                    {!! Form::file('attachments[]', ['id' => 'attachments', 'class' => 'form-control', 'multiple' => 'multiple']) !!}
                                </div>
                                @endcan
                            @endif
                        @endif
                        {!! Form::close() !!}
                    </div>
                </div>
                @if ($editMode)
                    <div class="row">
                        <div class="col-md-12">
                            <div class="nav-tabs-custom">
                                <ul class="nav nav-tabs">
                                    @can('notes.view')
                                    <li class="editable-tab active">
                                        <a id="notes-tab" data-toggle="tab" href="#tab-notes">
                                            {{ trans('fi.notes') }} <span
                                                    class="label label-default {!! $task->notes->count() <= 0 ? 'hide' : '' !!}"
                                                    id="notes-count">{{ $task->notes->count() }}</span>
                                        </a>
                                    </li>
                                    @endcan
                                    @can('attachments.view')
                                    <li class="editable-tab">
                                        <a id="attachments-tab" data-toggle="tab" href="#tab-attachments">
                                            {{ trans('fi.attachments') }} {!! $task->attachments->count() > 0 ? '<span class="label label-default">'.$task->attachments->count().'</span>' : '' !!}
                                        </a>
                                    </li>
                                    @endcan
                                </ul>
                                <div class="tab-content">
                                    @can('notes.view')
                                    <div id="tab-notes" class="tab-pane active">
                                        @include('notes._js_timeline', ['object' => $task, 'model' => 'FI\Modules\TaskList\Models\Task', 'hideHeader' => true, 'showPrivateCheckbox' => 0])
                                        <div id="note-timeline-container"></div>
                                    </div>
                                    @endcan
                                    @can('attachments.view')
                                    <div id="tab-attachments" class="tab-pane">
                                        @include('attachments._table', ['object' => $task, 'model' => 'FI\Modules\TaskList\Models\Task'])
                                    </div>
                                    @endcan
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

            </div>
            <div class="modal-footer">
                @if($editMode && isset($task->user) && $task->user->id != auth()->user()->id)
                    <span class="pull-left">{{ trans('fi.task_created_by_and_created_at',['created_by' => $task->user->name,'created_at' => $task->formatted_created_at]) }}</span>
                @endif
                <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('fi.cancel') }}</button>
                <button type="button" id="task-save-confirm" class="btn btn-primary"><i class="fa fa-save"></i>&nbsp;&nbsp;{{ trans('fi.save') }}
                </button>
            </div>
        </div>
    </div>
</div>