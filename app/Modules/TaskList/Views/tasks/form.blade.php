@extends('layouts.master')

@section('head')
    <script src='{{ asset('assets/plugins/daterangepicker/moment.js') }}'></script>
    @include('layouts._datepicker')
    @include('layouts._datetimepicker')
    @include('layouts._select2')
@stop

@section('javascript')
    <script type="text/javascript">
        $(function () {
            // Setup the ui

            $('#title').focus();

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

            $('.select2-select-box').select2();

            // Submit the form
            let formData = new FormData();
            $('#task-save-confirm').click(function () {
                let $form = $('#task-form');
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
                    let url = "{{route('task.edit',['id' => ':id'])}}"
                    url = url.replace(':id', response.task_id);
                    window.location = url;
                }).fail(function (xhr) {
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
@stop

@section('content')


    {!! Form::open(['route' => ($editMode) ? ['task.update', $task->id] : 'task.store', 'method' => 'post', 'role' => 'form', 'id' => 'task-form', 'files' => true]) !!}
    {!! Form::hidden('user_id', auth()->user()->id) !!}

    <section class="content-header">
        <h1 class="pull-left">
            @if ($editMode == true)
                {{ trans('fi.tasks') }} #{{ $task->id }}
            @else
                {{ trans('fi.task_form') }}
            @endif
        </h1>
        <div class="pull-right">
            <div class="btn-group">
                <a href="{{ $returnUrl }}" class="btn btn-default"><i
                            class="fa fa-backward"></i> {{ trans('fi.back') }}</a>
            </div>

            <button type="button" id="task-save-confirm" class="btn btn-primary"><i class="fa fa-save"></i> {{ trans('fi.save') }}</button>
        </div>
        <div class="clearfix"></div>
    </section>

    <section class="content">

        @include('layouts._alerts')

        <div class="row">

            <div class="col-md-12">

                <div class="box box-primary">

                    <div class="box-body">

                        <div class="row">
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

                        </div>
                    </div>

                </div>
                @if($editMode && isset($task))
                    <div class="row">

                    <div class="col-lg-12">
                        <div class="nav-tabs-custom">
                            <ul class="nav nav-tabs">
                                @can('notes.view')
                                    <li class="active"><a href="#tab-notes" data-toggle="tab">{{ trans('fi.notes') }} <span class="label label-default {!! $task->notes->count() <= 0 ? 'hide' : '' !!}" id="notes-count">{{ $task->notes->count() }}</span></a></li>
                                @endcan
                                @can('attachments.view')
                                    <li><a href="#tab-attachments" data-toggle="tab">{{ trans('fi.attachments') }} {!! $task->attachments->count() > 0 ? '<span class="label label-default attachment-count">'.$task->attachments->count().'</span>' : '' !!}</a></li>
                                @endcan
                            </ul>
                            <div class="tab-content">
                                @can('notes.view')
                                    <div class="tab-pane active" id="tab-notes">
                                        <div class="row">
                                            <div class="col-lg-12">
                                                @include('notes._js_timeline', ['object' => $task, 'model' => 'FI\Modules\TaskList\Models\Task', 'hideHeader' => true, 'showPrivateCheckbox' => 1])
                                                <div id="note-timeline-container"></div>
                                            </div>
                                        </div>
                                    </div>
                                @endcan
                                @can('attachments.view')
                                    <div class="tab-pane" id="tab-attachments">
                                        <div class="row">
                                            <div class="col-lg-12">
                                                @include('attachments._table', ['object' => $task, 'model' => 'FI\Modules\TaskList\Models\Task'])
                                            </div>
                                        </div>
                                    </div>
                                @endcan
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>

        </div>

    </section>

@stop