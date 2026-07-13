@foreach($tasks as $section => $sectionData)
    @if($section == 0 && count($sectionData['tasks']) == 0)
    @else
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
                                <span class="task-list-smaller-font" style="padding-left: 15px;"> {!! $task->client ? '<a href="'.route('clients.show',$task->client).'">'.$task->client->name.'</a>' : '' !!}</span>
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
    @endif
@endforeach
