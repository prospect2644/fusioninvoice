@foreach ($object->notes()->protect(auth()->user())->with('user')->orderBy('created_at', 'desc')->get() as $note)
    <div class="direct-chat-msg" id="note-{{ $note->id }}">
        <div class="direct-chat-info clearfix">
            <span class="direct-chat-name pull-left">{{ $note->user->name }}</span>
            <span class="direct-chat-scope pull-right">
                @if (!auth()->user()->client_id)
                    @can('notes.delete')
                    <a href="javascript:void(0)" class="delete-note" data-note-id="{{ $note->id }}">{{ trans('fi.delete') }}</a>
                    @endcan
                @endif
            </span>
            @if (isset($showPrivateCheckbox) and $showPrivateCheckbox == true)
                <span class="direct-chat-scope pull-right">
                @if ($note->private)
                        <span class="label label-danger">{{ trans('fi.private') }}</span>
                    @else
                        <span class="label label-success">{{ trans('fi.public') }}</span>
                    @endif
            </span>
            @endif
            <span class="direct-chat-timestamp pull-right" title="{{ $note->formatted_created_at_system_format }}">
                {{ $note->formatted_created_at }}
            </span>
        </div>
        <img class="direct-chat-img" src="{{ profileImageUrl($note->user) }}" alt="{{ $note->user->name }}">
        <div class="direct-chat-text">
            {!! $note->formatted_note !!}
        </div>
    </div>
@endforeach