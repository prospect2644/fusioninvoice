@extends('layouts.master')

@section('content')

    <script type="text/javascript">
        $(function () {

            alertify.defaults.theme.ok = "ui negative button";
            alertify.defaults.theme.cancel = "ui black button";

            $("<style>").text(".ajs-header{ background-color: #ba0606 !important; }").appendTo($("body"));

            $('#btn-delete-task').on('click', function () {
                let url = $(this).data('action');

                alertify.confirm("{!! trans('fi.delete_record_warning') !!}", function () {
                    $.get(url).done(function () {
                        window.location.replace('{{ route('task.index') }}');
                    });
                }, function () {
                    alertify.alert().destroy();
                }).setHeader(confirmHeader).set({transition: 'zoom', defaultFocus: 'cancel'});
            });
        });
    </script>

    <section class="content-header">
        <h1 class="pull-left">
            {{ trans('fi.view_task') }}
        </h1>

        <div class="pull-right">

            <a href="{{ $returnUrl }}" class="btn btn-default">
                <i class="fa fa-backward"></i> {{ trans('fi.back') }}
            </a>

            @if($task->user_id == $me->id)
                <a id="task-edit-btn" href="{{ route('task.edit', [$task->id]) }}" class="btn btn-default">{{ trans('fi.edit') }}</a>
                <a class="btn btn-danger" href="#" data-action="{{ route('task.delete', [$task->id]) }}" id="btn-delete-task"><i class="fa fa-trash"></i> {{ trans('fi.delete') }}</a>
            @endif
        </div>
        <div class="clearfix"></div>
    </section>

    <section class="content">

        @include('layouts._alerts')

        <div class="row">

            <div class="col-xs-12">

                <div class="nav-tabs-custom">
                    <ul class="nav nav-tabs">
                        <li class="active editable-tab" data-edit-link="{{ route('task.edit', [$task->id, 'tab' => 'general']) }}"><a id="general-tab" data-toggle="tab" href="#tab-details">{{ trans('fi.details') }}</a></li>
                        @can('attachments.view')
                            <li class="editable-tab" data-edit-link="{{ route('task.edit', [$task->id, 'tab' => 'attachments']) }}"><a id="attachments-tab" data-toggle="tab" href="#tab-attachments">{{ trans('fi.attachments') }} <span class="label attachment-count label-default {!! $task->attachments->count() <= 0 ? 'hide' : '' !!}" id="attachment-count">{{ $task->attachments->count() }}</span></a></li>
                        @endcan
                        @can('notes.view')
                            <li class="editable-tab" data-edit-link="{{ route('task.edit', [$task->id, 'tab' => 'notes']) }}"><a id="notes-tab" data-toggle="tab" href="#tab-notes">{{ trans('fi.notes') }} <span class="label note-count label-default {!! $task->notes->count() <= 0 ? 'hide' : '' !!}" id="notes-count">{{ $task->notes->count() }}</span></a></li>
                        @endcan
                    </ul>
                    <div class="tab-content">

                        <div id="tab-details" class="tab-pane active">

                            <div class="row" style="margin-top: 5px;">

                                <div class="col-md-12">

                                    <table class="table table-striped">
                                        <tr>
                                            <td class="col-md-2"><label>{{ trans('fi.title') }}</label></td>
                                            <td class="col-md-10">{!! $task->title !!}</td>
                                        </tr>
                                        <tr>
                                            <td class="col-md-2"><label>{{ trans('fi.description') }}</label></td>
                                            <td class="col-md-10">{!! $task->description !!}</td>
                                        </tr>
                                        <tr>
                                            <td class="col-md-2"><label>{{ trans('fi.due_date') }}</label></td>
                                            <td class="col-md-10">{!! $task->formatted_due_date !!}</td>
                                        </tr>
                                        <tr>
                                            <td class="col-md-2"><label>{{ trans('fi.task_section') }}</label></td>
                                            <td class="col-md-10">{!! $task->taskSection->name !!}</td>
                                        </tr>
                                        <tr>
                                            <td class="col-md-2"><label>{{ trans('fi.assignee') }}</label></td>
                                            <td class="col-md-10">{!! $task->assignee->formatted_name !!}</td>
                                        </tr>
                                        <tr>
                                            <td class="col-md-2"><label>{{ trans('fi.client') }}</label></td>
                                            <td class="col-md-10">{!! ($task->client) ? $task->client->name : '' !!}</td>
                                        </tr>
                                        @if($task->user_id != auth()->user()->id)
                                            <tr>
                                                <td class="col-md-2"><label>{{ trans('fi.created_by') }}</label></td>
                                                <td class="col-md-10">{!! ($task->user) ? $task->user->name : '' !!}</td>
                                            </tr>
                                            <tr>
                                                <td class="col-md-2"><label>{{ trans('fi.created_at') }}</label></td>
                                                <td class="col-md-10">{!! ($task->user) ? $task->formatted_created_at : '' !!}</td>
                                            </tr>
                                        @endif
                                    </table>
                                </div>

                            </div>

                        </div>

                        @can('attachments.view')
                        <div class="tab-pane" id="tab-attachments">
                            @include('attachments._table', ['object' => $task, 'model' => 'FI\Modules\TaskList\Models\Task'])
                        </div>
                        @endcan

                        @can('notes.view')
                            <div id="tab-notes" class="tab-pane">
                                @include('notes._js_timeline', ['object' => $task, 'model' => 'FI\Modules\TaskList\Models\Task', 'hideHeader' => true, 'showPrivateCheckbox' => 0])
                                <div id="note-timeline-container"></div>
                            </div>
                        @endcan
                    </div>
                </div>

            </div>

        </div>
    </section>

@stop
