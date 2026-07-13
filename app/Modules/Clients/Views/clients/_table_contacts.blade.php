<script type="text/javascript">
    $(function () {
        $('.btn-edit-contact').click(function () {
            $('#modal-placeholder').load($(this).data('url'));
        });

        $('.btn-delete-contact').click(function () {
            var $_this = $(this);
            alertify.defaults.theme.ok = "ui negative button";
            alertify.defaults.theme.cancel = "ui black button";
            $("<style>").text(".ajs-header{ background-color: #ba0606 !important; }").appendTo($("body"));

            alertify.confirm('{{ trans('fi.delete_record_warning') }}', function () {
                $.post('{{ route('clients.contacts.delete', [$client->id]) }}', {
                    id: $_this.data('contact-id')
                }).done(function (response) {
                    $('#tab-contacts').html(response);
                });
            }, function () {
                alertify.alert().destroy();
            }).setHeader(confirmHeader).set({transition: 'zoom', defaultFocus: 'cancel'});
        });

        $('.update-default').click(function () {
            $.post('{{ route('clients.contacts.updateDefault', [$client->id]) }}', {
                id: $(this).data('contact-id'),
                default: $(this).data('default')
            }).done(function (response) {
                $('#tab-contacts').html(response);
            });
        });
    });
</script>

<div class="row">
    <div class="col-lg-12">
        <table class="table table-hover table-striped">
            <thead>
            <tr>
                <th>{{ trans('fi.title') }}</th>
                <th>{{ trans('fi.name') }}</th>
                <th>{{ trans('fi.email') }}</th>
                <th>{{ trans('fi.primary_phone') }}</th>
                <th>{{ trans('fi.notes') }}</th>
                <th>{{ trans('fi.default_to') }}</th>
                <th>{{ trans('fi.default_cc') }}</th>
                <th>{{ trans('fi.default_bcc') }}</th>
                @if(Gate::check('contacts.update') || Gate::check('contacts.delete'))
                    <th>{{ trans('fi.options') }}</th>
                @endif
            </tr>
            </thead>
            <tbody>
            <?php foreach ($client->contacts as $contact) { ?>
            <tr>
                <td>{{ $contact->title ? trans('fi.'.$contact->title) : '' }}</td>
                <td>{{ $contact->name }}</td>
                <td>{{ $contact->email }}</td>
                <td>{{ $contact->primary_phone }}</td>
                <td>{!! $contact->formatted_notes !!}</td>
                <td>{{ $contact->formatted_default_to }}</td>
                <td>{{ $contact->formatted_default_cc }}</td>
                <td>{{ $contact->formatted_default_bcc }}</td>
                @if(Gate::check('contacts.update') || Gate::check('contacts.delete'))
                    <td>
                        <div class="btn-group">
                            <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown">
                                {{ trans('fi.options') }} <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-right">
                                @can('contacts.update')
                                <li><a href="javascript:void(0)" class="btn-edit-contact"
                                       data-url="{{ route('clients.contacts.edit', [$contact->client_id, $contact->id]) }}"><i
                                                class="fa fa-edit"></i> {{ trans('fi.edit') }}</a></li>
                                @endcan
                                @can('contacts.delete')
                                <li><a href="javascript:void(0)" class="btn-delete-contact text-danger"
                                       data-contact-id={{ $contact->id }}><i
                                                class="fa fa-trash-o"></i> {{ trans('fi.delete') }}</a></li>
                                @endcan
                            </ul>
                        </div>
                    </td>
                @endif
            </tr>
            <?php } ?>
            </tbody>
        </table>

    </div>
</div>
