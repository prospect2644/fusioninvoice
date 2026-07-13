@if (!config('app.demo'))
    <script type="text/javascript">

        $(function () {
            $('#modal-attach-files').modal({
                backdrop: 'static',
                keyboard: false
            });

            $('#input-attachments').change(function () {
                alertify.defaults.theme.ok = "ui negative button";
                alertify.defaults.theme.cancel = "ui black button";

                if ((this.files[0].size > 8000000)) {
                    $('#input-attachments').val('');
                    return alertify.error("{{ trans('fi.attachment_error', ['size' => '8MB']) }}", 5);
                }
                else if ((this.files[0].size > 2000000) && (this.files[0].size < 8000000)) {
                    let attechment_warning = "{{ trans('fi.attachment_warning') }}";
                    var sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
                    let bytes = this.files[0].size;
                    let i = parseInt(Math.floor(Math.log(bytes) / Math.log(1024)));
                    let size = Math.round(bytes / Math.pow(1024, i), 2) + ' ' + sizes[i];
                    attechment_warning = attechment_warning.replace(':size', size);
                    alertify.confirm(attechment_warning, function () {
                        return startUpload();
                    }, function () {
                        $('#input-attachments').val('');
                        alertify.alert().destroy();
                    }).setHeader(confirmHeader).set({transition: 'zoom', defaultFocus: 'cancel'});
                }
                else {
                    return startUpload();
                }
            });

            function startUpload() {
                formData = new FormData(document.forms.namedItem('form-attachments'));
                formData.append('model', '{{ addslashes($model) }}');
                formData.append('model_id', '{{ $modelId }}');

                $('#input-attachments').attr('disabled', 'disabled');
                resetProgressBar('0%', '0%');
                $('#attachment-upload-progress').show();

                $.ajax({
                    url: '{{ route('attachments.ajax.upload') }}',
                    type: 'POST',
                    data: formData,
                    xhr: function () {
                        var myXhr = $.ajaxSettings.xhr();
                        if (myXhr.upload) {
                            myXhr.upload.addEventListener('progress', progress, false);
                        }
                        return myXhr;
                    },
                    cache: false,
                    contentType: false,
                    processData: false,
                    success: function () {
                        $('#input-attachments').val('');
                        $('#attachments-list').load("{{ route('attachments.ajax.list') }}", {
                            model: '{{ addslashes($model) }}',
                            model_id: '{{ $modelId }}'
                        });
                        $('#input-attachments').removeAttr('disabled');
                        $('#modal-attach-files').modal('hide');
                        let attachmentCount = Number($('#attachments-list table tr').length);
                        if (0 < attachmentCount) {
                            $('.attachment-count').html(Number(attachmentCount)).show().removeClass('hide');
                        } else {
                            $('.attachment-count').html('').hide().addClass('hide');
                        }
                    },
                    error: function (XMLHttpRequest, textStatus, error) {
                        if (XMLHttpRequest.status == 422) {
                            $("#attachment-upload-progress-bar").addClass('progress-bar-danger').html(XMLHttpRequest.responseJSON.message);
                        }
                        else {
                            $("#attachment-upload-progress-bar").addClass('progress-bar-danger').html(error);
                        }
                        $('#input-attachments').removeAttr('disabled');
                    }
                });
            }

            function progress(e) {
                if (e.lengthComputable) {
                    var max = e.total;
                    var current = e.loaded;
                    var percentage = Math.round((current * 100) / max);
                    $("#attachment-upload-progress-bar").css("width", percentage + '%').html(percentage + '%');

                    if (percentage == 100) {
                        resetProgressBar('100%', '{{ trans('fi.complete') }}');
                        $('#attachment-upload-progress-bar').addClass('progress-bar-success').html('{{ trans('fi.complete') }}');
                    }
                }
            }

            function resetProgressBar(width, text) {
                $('#attachment-upload-progress-bar')
                        .removeClass('progress-bar-danger')
                        .removeClass('progress-bar-success')
                        .css('width', width)
                        .html(text);
            }

        });
    </script>

    <div class="modal fade" id="modal-attach-files">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">{{ trans('fi.attach_files') }}</h4>
                </div>
                <div class="modal-body">

                    <p class="text-bold">{{ trans('fi.attach_files') }}</p>

                    <form method="post" enctype="multipart/form-data" name="form-attachments" id="form-attachments" style="margin-bottom: 10px;">
                        <input type="file" name="attachments[]" id="input-attachments" multiple>
                    </form>

                    <div style="display: none;" id="attachment-upload-progress">
                        <p class="text-bold">{{ trans('fi.upload_progress') }}</p>

                        <div class="progress">
                            <div id="attachment-upload-progress-bar" class="progress-bar" role="progressbar" style="width: 0;">
                                0%
                            </div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('fi.cancel') }}</button>
                </div>
            </div>
        </div>
    </div>
@endif