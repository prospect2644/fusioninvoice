<script type="text/javascript">
    $(function () {
        alertify.defaults.theme.ok = "ui negative button";
        alertify.defaults.theme.cancel = "ui black button";
        $('.attachment-count').html("{{$object->attachments()->count()}}")
        $("<style>").text(".ajs-header{ background-color: #ba0606 !important; }").appendTo($("body"));

        $('.btn-delete-attachment').click(function () {
            var attachment_id = $(this).data('attachment-id');

            alertify.confirm("{!! trans('fi.delete_record_warning') !!}", function () {
                $.post("{{ route('attachments.ajax.delete') }}", {
                    model: '{{ addslashes($model) }}',
                    model_id: '{{ $object->id }}',
                    attachment_id: attachment_id
                }, function () {
                    $('#attachments-list').load("{{ route('attachments.ajax.list') }}", {
                        model: '{{ addslashes($model) }}',
                        model_id: '{{ $object->id }}'
                    }, function () {
                        let attachmentCount = Number($('#attachments-list table tr').length) - 1;
                        if (0 < attachmentCount) {
                            $('.attachment-count').html(Number(attachmentCount)).show().removeClass('hide');
                        } else {
                            $('.attachment-count').html('').hide().addClass('hide');
                        }
                    });
                });
            }, function () {
                alertify.alert().destroy();
            }).setHeader(confirmHeader).set({transition: 'zoom', defaultFocus: 'cancel'});

        });

        $('.client-visibility').change(function () {
            $.post('{{ route('attachments.ajax.access.update') }}', {
                client_visibility: $(this).val(),
                attachment_id: $(this).data('attachment-id')
            });
        });

        $('#btn-attach-files').click(function () {
            $('#attachment-modal-placeholder').load('{{ route('attachments.ajax.modal') }}', {
                model: '{{ addslashes($model) }}',
                model_id: '{{ $object->id }}'
            });
        });
    });
</script>

<div id="attachments-list">

    @if (!config('app.demo'))
        @can('attachments.create')
        <a href="javascript:void(0)" class="btn btn-primary btn-sm" id="btn-attach-files">{{ trans('fi.attach_files') }}</a>
        <p style="margin-top: 4px;">
            <small>{{trans('fi.note')}}:&nbsp;{{trans('fi.attachment_notice',['size' => '8MB'])}}</small>
        </p>
        @endcan
    @else
        <a href="javascript:void(0)" class="btn btn-primary btn-sm">File attachments are disabled in the demo</a>
    @endif

    <table class="table table-condensed">
        <thead>
        <tr>
            <th>{{ trans('fi.attachment') }}</th>
            @if(!$object instanceof FI\Modules\TaskList\Models\Task)
                <th>{{ trans('fi.client_visibility') }}</th>
            @endif
            <th></th>
        </tr>
        </thead>
        <tbody>
        @foreach ($object->attachments()->orderBy('created_at', 'desc')->get() as $attachment)
            <tr>
                <td><a href="{{ $attachment->download_url }}">{{ $attachment->filename }}</a></td>
                @if(!$object instanceof FI\Modules\TaskList\Models\Task)
                    <td>
                        <div class="row">
                            <div class="col-md-5">
                                {!! Form::select('', $object->attachment_permission_options, $attachment->client_visibility, ['class' => 'form-control client-visibility input-sm', 'data-attachment-id' => $attachment->id]) !!}
                            </div>
                        </div>
                    </td>
                @endif
                <td>
                    @can('attachments.delete')
                    <a class="btn btn-xs btn-danger btn-delete-attachment" href="javascript:void(0);"
                       title="{{ trans('fi.delete') }}" data-attachment-id="{{ $attachment->id }}">
                        <i class="fa fa-times"></i>
                    </a>
                    @endcan
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>