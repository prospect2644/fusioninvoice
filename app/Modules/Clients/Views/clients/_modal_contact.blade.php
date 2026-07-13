<script type="text/javascript">
    $(function () {
        var modalContact = $('#modal-contact');

        modalContact.modal();

        $('#btn-contact-submit').click(function () {
            $.post("{{ $submitRoute }}", $('#client-contact').serialize()).fail(function (response) {
                showAlertifyErrors($.parseJSON(response.responseText).errors);
            }).done(function (response) {
                modalContact.modal('hide');
                @if ($editMode)
                    alertify.success('{{ trans('fi.contact_updated') }}', 5);
                @else
                    alertify.success('{{ trans('fi.contact_added') }}', 5);
                @endif
                $('#tab-contacts').html(response);
            });
        });
    });
</script>

<div class="modal" id="modal-contact">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">
                    @if ($editMode)
                        {{ trans('fi.edit_contact') }}
                    @else
                        {{ trans('fi.add_contact') }}
                    @endif
                </h4>
            </div>
            <div class="modal-body">

                <div id="modal-status-placeholder"></div>

                <form class="form-horizontal" id="client-contact" name="client-contact">

                    <div class="form-group">
                        <label class="col-sm-3 control-label">{{ trans('fi.title') }}:</label>

                        <div class="col-sm-3">
                            {!! Form::select('title', $contactTitle, ($editMode) ? $contact->title : null, ['id' => 'title', 'class' => 'form-control']) !!}
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label">{{ trans('fi.name') }}:</label>

                        <div class="col-sm-9">
                            {!! Form::text('name', ($editMode) ? $contact->name : null, ['class' => 'form-control', 'id' => 'contact_name']) !!}
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label">{{ trans('fi.email') }}:</label>

                        <div class="col-sm-9">
                            {!! Form::text('email', ($editMode) ? $contact->email : null, ['class' => 'form-control', 'id' => 'contact_email']) !!}
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label">{{ trans('fi.primary_phone') }}:</label>

                        <div class="col-sm-9">
                            {!! Form::text('primary_phone', ($editMode) ? $contact->primary_phone : null, ['class' => 'form-control', 'id' => 'primary_phone']) !!}
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label">{{ trans('fi.alternate_phone') }}:</label>

                        <div class="col-sm-9">
                            {!! Form::text('alternate_phone', ($editMode) ? $contact->alternate_phone : null, ['class' => 'form-control', 'id' => 'alternate_phone']) !!}
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label">{{ trans('fi.notes') }}:</label>

                        <div class="col-sm-9">
                            {!! Form::textarea('notes', ($editMode) ? $contact->notes : null, ['class' => 'form-control', 'id' => 'contact_notes', 'rows' => 3]) !!}
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label">{{ trans('fi.default_to') }}:</label>

                        <div class="col-sm-3">
                            {!! Form::select('default_to', ['0' => trans('fi.no'), '1' => trans('fi.yes')], ($editMode) ? $contact->default_to : null, ['id' => 'default_to', 'class' => 'form-control']) !!}
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label">{{ trans('fi.default_cc') }}:</label>

                        <div class="col-sm-3">
                            {!! Form::select('default_cc', ['0' => trans('fi.no'), '1' => trans('fi.yes')], ($editMode) ? $contact->default_cc : null, ['id' => 'default_cc', 'class' => 'form-control']) !!}
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label">{{ trans('fi.default_bcc') }}:</label>

                        <div class="col-sm-3">
                            {!! Form::select('default_bcc', ['0' => trans('fi.no'), '1' => trans('fi.yes')], ($editMode) ? $contact->default_bcc : null, ['id' => 'default_bcc', 'class' => 'form-control']) !!}
                        </div>
                    </div>
                    {!! Form::hidden('client_id', $clientId) !!}
                </form>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('fi.cancel') }}</button>
                <button type="button" id="btn-contact-submit" class="btn btn-primary">{{ trans('fi.save') }}</button>
            </div>
        </div>
    </div>
</div>
