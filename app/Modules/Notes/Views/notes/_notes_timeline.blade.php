<script type="text/javascript">
    $(document).ready(function ($) {
        $('#add-timeline-note').click(function () {
            $('#note-modal-placeholder').load('{{ route('notes.create') }}');
        });

        @if (!auth()->user()->client_id)

        alertify.defaults.theme.ok = "ui negative button";
        alertify.defaults.theme.cancel = "ui black button";
        $("<style>").text(".ajs-header{ background-color: #ba0606 !important; }").appendTo($("body"));

        $('.note-item-edit').click(function () {
            let editLink = $(this).data('edit-link');
            $('#note-modal-placeholder').load(editLink);
        });

        $('.note-item-delete').click(function () {
            let $ele = $(this);
            alertify.confirm("{!! trans('fi.delete_record_warning') !!}", function () {
                let noteId = $ele.data('note-id');
                $('#note-' + noteId).hide();
                $('#note-timeline-item-' + noteId).hide();
                let notesCount = Number($('#notes-count').text()) - 1;
                if (0 < notesCount) {
                    $('#notes-count,.note-count').html(Number(notesCount)).show().removeClass('hide');
                } else {
                    $('#notes-count,.note-count').html('').hide().addClass('hide');
                }

                $.post("{{ route('notes.delete') }}", {
                    id: noteId
                });
                if (typeof $.fn.loadTimelineList == 'function') {
                    $.fn.loadTimelineList();
                }
            }, function () {
                alertify.alert().destroy();
            }).setHeader(confirmHeader).set({transition: 'zoom', defaultFocus: 'cancel'});

        });
        @endif

        $('.custom-search').click(function () {
                    $("#notes-filter-form").submit();
                    $('#modal-search-config').modal('hide');
                });

        $('.close-search-config-modal').click(function () {
            $('#modal-search-config').modal('hide');
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

        $('#notes-filter-form').submit(function (e) {
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
                    $('#note-timeline-container').html(response);
                });
            }
        });

        $('#btn-clear-notes-filter').click(function () {
            $('#note-timeline-container').load('{{ route('notes.list', [$model, $object->id, $showPrivateCheckbox, 'description' => !Session::has('filter_by_description') ? '1' : (Session::get('filter_by_description') == 1 ? '1' : '0'), 'tags' => !Session::has('filter_by_tags') ? '1' : (Session::get('filter_by_tags') == 1 ? '1' : '0'), 'username' => !Session::has('filter_by_username') ? '1' : (Session::get('filter_by_username') == 1 ? '1' : '0')]) }}');
        });

        $('.note-collapsed').click(function () {
            if ($(this).attr('aria-expanded') == 'false') {
                var text = '{{ trans("fi.show_less") }}';
            } else {
                var text = '{{ trans("fi.show_more") }}';
            }
            $(this).text(text);
        });
    });
</script>

<div class="row" id="fi-notepad" data-model="{{ $model }}" data-object-id="{{ $object->id }}"
     data-can-be-private="{{ $showPrivateCheckbox }}">
    <div class="col-xs-12">

        <div class="row">
            <div class="col-xs-12 pd-none">
                <div class="col-md-5">
                    <h2 style="display:inline;"><i class="fa fa-comments-o"></i> {{ trans('fi.notepad') }}</h2>
                    @if($notes->total() > 0)
                        <span class="label label-default note-count"
                              style="margin-left: 8px;">{{ $notes->total() }}</span>
                    @endif
                </div>
                <div class="col-md-7">
                    <div class="pull-right">
                        {!! Form::open(['method' => 'GET', 'url' => route('notes.list', [$model, $object->id, $showPrivateCheckbox]), 'id' => 'notes-filter-form', 'class' => 'form-inline inline']) !!}
                        <div class="input-group">
                            <span class="input-group-btn">
                                <button type="button" data-toggle="modal" data-target="#modal-search-config"
                                        id="search-config-btn" class="btn btn-flat btn-sm"><i
                                            class="fa fa-ellipsis-v"></i></button>
                            </span>
                            {!! Form::text('search', request('search'), ['id' =>'search-notes', 'class' => 'form-control inline input-sm','placeholder' => trans('fi.search')]) !!}
                            <span class="input-group-btn">
                                <button type="submit" id="search-btn" class="btn btn-flat btn-sm btn-primary"><i
                                            class="fa fa-search"></i></button>
                            </span>
                        </div>
                        @include('notes._notes_search_config_modal')
                        {!! Form::close() !!}
                        @can('notes.create')
                        <button type="button" class="btn btn-primary btn-sm" id="add-timeline-note"><i
                                    class="fa fa-plus"></i> {{ trans('fi.add_note') }} </button>
                        @endcan
                    </div>
                </div>
            </div>
        </div>
        <hr>
        <div class="row">
            <div class="col-md-12">
                <ul class="timeline">
                    @foreach ($notes as $note)
                        <li id="note-timeline-item-{{ $note->id }}">
                            <i class="fa initials">
                                {!! $note->user != null ? $note->user->getAvatar() : '' !!}
                            </i>

                            <div class="timeline-item">
                                <div class="timeline-head">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <span class="timeline-heading">{!! $note->user != '' ? $note->user->formatted_name : '' !!}</span>
                                            <span class="time-item"
                                                  title="{{ $note->formatted_created_at_system_format }}">
                                                {{ trans('fi.added') }}: {{ $note->formatted_created_at }}
                                            </span>
                                        </div>
                                        <div class="col-md-6">
                                            @if($note->updatedBy)
                                                <span class="time-item"
                                                      title="{{ $note->formatted_updated_at_system_format }}">
                                                {{ trans('fi.last_edited') }}
                                                    : {{ $note->updatedBy->name }} {{ $note->formatted_updated_at }}
                                            </span>
                                            @endif
                                            <span class="note-tags pull-right">
                                            @if (isset($showPrivateCheckbox) and $showPrivateCheckbox == true)
                                                    @if ($note->private)
                                                        <span class="label label-danger">{{ trans('fi.private') }}</span>
                                                    @else
                                                        <span class="label label-success">{{ trans('fi.public') }}</span>
                                                    @endif
                                                @endif
                                                @foreach($note->tags as $noteTag)
                                                    <span class="label label-default">{{ $noteTag->tag->name }}</span>
                                                @endforeach
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="timeline-body">
                                    {!! $note->formatted_note !!}
                                </div>
                                <div class="timeline-footer">
                                    <div class="pull-right">
                                        @if(Gate::check('notes.update') || Gate::check('notes.delete'))
                                            @can('notes.update')
                                            <a href="#" class="btn btn-primary btn-xs note-item-edit"
                                               data-edit-link="{{ route('notes.edit', ['id' => $note->id]) }}">
                                                <i class="fa fa-edit" title="{{ trans('fi.edit') }}"></i>
                                            </a>
                                            @endcan
                                            @can('notes.delete')
                                            <a href="#" class="btn btn-danger btn-xs note-item-delete"
                                               data-note-id="{{ $note->id }}">
                                                <i class="fa fa-trash" title="{{ trans('fi.delete') }}"></i>
                                            </a>
                                            @endcan
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
                <div class="pull-left" style="padding-left: 15px;;">
                    @if(request('search'))
                        <i class="fa fa-filter"></i> {{ trans('fi.n_records_match', ['label' => $notes->total(),'plural' => $notes->total() > 1 ? 's' : '']) }}
                        <button type="button" class="btn btn-link"
                                id="btn-clear-notes-filter">{{ trans('fi.clear') }}</button>
                    @endif
                </div>
                <div class="pull-right" id="notes-pagination" style="padding-right: 25px;">
                    {{ $notes->links() }}
                </div>
            </div>
        </div>

    </div>
</div>
