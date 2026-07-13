<table class="table table-hover table-striped">

    <thead>
    <tr>
        @if(isset($bulk_action) && $bulk_action == true)
            <th class="col-md-1">{!! Sortable::link('id', trans('fi.id')) !!}</th>
            <th class="col-md-2">{!! Sortable::link('title', trans('fi.title')) !!}</th>
            <th class="col-md-2">{!! Sortable::link('description', trans('fi.description')) !!}</th>
            <th class="col-md-2">{!! Sortable::link('due_date', trans('fi.due_date')) !!}</th>
            <th class="col-md-2">{!! Sortable::link('due_date', trans('fi.assignee')) !!}</th>
            <th class="col-md-2">{!! trans('fi.status') !!}</th>
            <th class="col-md-1">{!! trans('fi.options') !!}</th>
        @else
            <th class="col-md-1">{!! trans('fi.id') !!}</th>
            <th class="col-md-2">{!! trans('fi.title') !!}</th>
            <th class="col-md-2">{!! trans('fi.description') !!}</th>
            <th class="col-md-2">{!! trans('fi.due_date') !!}</th>
            <th class="col-md-2">{!! trans('fi.assignee') !!}</th>
            <th class="col-md-2">{!! trans('fi.status') !!}</th>
            <th class="col-md-1">{!! trans('fi.options') !!}</th>
        @endif
    </tr>
    </thead>

    <tbody>
    @foreach ($tasks as $task)
        <tr>
            @if(isset($bulk_action) && $bulk_action == true)
                <td class="{{(($task->assignee_id == auth()->user()->id) && ($task->user_id == auth()->user()->id)) ? 'column-task-assigned-to-me' : ''}}">
                    <input type="checkbox" class="bulk-record" data-id="{{ $task->id }}">
                </td>
            @else
                <td>{{ $task->id }}</td>
                @endcan
                <td>
                    @if($task->user_id == auth()->user()->id || $task->assignee_id == auth()->user()->id)
                        <a href="{{ route('task.show', $task->id) }}">{{ $task->title  }}</a>
                    @else
                        {{ $task->title  }}
                    @endif
                </td>
                <td>{!! $task->description !!}</td>
                <td>{!! $task->formatted_due_date !!}</td>
                <td>{{$task->formatted_assignee}}</td>
                <td>{{$task->is_complete == 1 ? trans('fi.transition.completed') : trans('fi.open')}}</td>
                @if(isset($bulk_action) && $bulk_action == true)
                    <td>
                        <div class="btn-group">
                            <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown">
                                {{ trans('fi.options') }} <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-right">
                                <li><a href="{{ route('task.show', [$task->id]) }}" id="view-task-{{ $task->id }}"><i
                                                class="fa fa-search"></i> {{ trans('fi.view') }}</a></li>
                                @if($task->is_complete == 0)
                                    <li>
                                        <a href="javascript:void(0)" class="action-complete"
                                           data-task-id="{{ $task->id }}" data-redirect-to="{{ request()->fullUrl() }}">
                                            <i class="fa fa-check"></i> {{ trans('fi.complete') }}
                                        </a>
                                    </li>
                                @else
                                    <li>
                                        <a href="javascript:void(0)" class="action-reopen"
                                           data-task-id="{{ $task->id }}" data-redirect-to="{{ request()->fullUrl() }}">
                                            <i class="fa fa-check"></i> {{ trans('fi.reopen_task') }}
                                        </a>
                                    </li>
                                @endif
                                @if($task->user_id == auth()->user()->id || $task->assignee_id == auth()->user()->id)
                                    <li>
                                        <a href="{{ route('task.edit', [$task->id]) }}">
                                            <i class="fa fa-edit"></i> {{ trans('fi.edit') }}
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#" data-action="{{ route('task.delete', [$task->id]) }}"
                                           data-redirect-to="{{ request()->fullUrl() }}"
                                           data-task-id="{{ $task->id }}"
                                           class="action-delete text-danger">
                                            <i class="fa fa-trash-o"></i> {{ trans('fi.delete') }}
                                        </a>
                                    </li>
                                @endif
                            </ul>
                        </div>
                    </td>
                @else
                    @if($task->user_id == auth()->user()->id || $task->assignee_id == auth()->user()->id)
                        <td>
                            <div class="btn-group">
                                <button type="button" class="btn btn-default btn-sm dropdown-toggle"
                                        data-toggle="dropdown">
                                    {{ trans('fi.options') }} <span class="caret"></span>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-right">
                                    <li><a href="{{ route('task.show', [$task->id]) }}"
                                           id="view-task-{{ $task->id }}"><i
                                                    class="fa fa-search"></i> {{ trans('fi.view') }}</a></li>
                                    @if($task->is_complete == 0)
                                        <li>
                                            <a href="javascript:void(0)" class="action-complete"
                                               data-task-id="{{ $task->id }}"
                                               data-tab="tasks"
                                               data-redirect-to="{{ request()->fullUrl() }}">
                                                <i class="fa fa-check"></i> {{ trans('fi.complete') }}
                                            </a>
                                        </li>
                                    @else
                                        <li>
                                            <a href="javascript:void(0)" class="action-reopen"
                                               data-task-id="{{ $task->id }}"
                                               data-tab="tasks"
                                               data-redirect-to="{{ request()->fullUrl() }}">
                                                <i class="fa fa-check"></i> {{ trans('fi.reopen_task') }}
                                            </a>
                                        </li>
                                    @endif
                                    <li>
                                        <a href="{{ route('task.edit', [$task->id]) }}">
                                            <i class="fa fa-edit"></i> {{ trans('fi.edit') }}
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#" data-action="{{ route('task.delete', [$task->id]) }}"
                                           data-redirect-to="{{ request()->fullUrl() }}"
                                           data-task-id="{{ $task->id }}"
                                           data-tab="tasks"
                                           class="action-delete text-danger">
                                            <i class="fa fa-trash-o"></i> {{ trans('fi.delete') }}
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </td>
                    @endif
                @endif
        </tr>
    @endforeach
    </tbody>

</table>