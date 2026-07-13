@extends('layouts.master')

@section('javascript')
    <script type="text/javascript">
        $(function () {

            alertify.defaults.theme.ok = "ui negative button";
            alertify.defaults.theme.cancel = "ui black button";

            $("<style>")
                    .text(".ajs-header{ background-color: #ba0606 !important; }")
                    .appendTo($("body"));

            $('#btn-delete-client').click(function () {
                var $_this = $(this);

                alertify.confirm("{!! trans('fi.delete_client_warning') !!}", function () {
                    $.get($_this.data('action')).done(function (response) {
                        if (response.success == true) {
                            alertify.success(response.message, 5);
                            window.location = decodeURIComponent('{{ route('clients.index') }}');
                        }
                    }).fail(function (response) {
                        if (response.status == 400) {
                            showAlertifyErrors($.parseJSON(response.responseText).errors);
                        }
                        else {
                            alertify.error('{{ trans('fi.unknown_error') }}', 5);
                        }
                    });
                }, function () {
                    alertify.alert().destroy();
                }).setHeader(confirmHeader).set({transition: 'zoom', defaultFocus: 'cancel'});
            });

            $('#btn-add-contact').click(function () {
                var $clientId = '{{$client->id}}';
                var $createContactUrl = '{{ route("clients.contacts.create", ":client_id") }}';
                $createContactUrl = $createContactUrl.replace(':client_id', $clientId);
                $('#modal-placeholder').load($createContactUrl);
            });

            var importantNoteHeader = '<span style="color:white;"> <span class="fa fa-bell-o fa-2x"'
                    + 'style="vertical-align:middle;padding-right:10px;">'
                    + '</span>' + '{!! trans('fi.important') !!}' + '</span>';

            @if (!empty($client->important_note) && !str_contains(URL::previous(), 'edit'))
                alertify.alert().setHeader(importantNoteHeader).set({transition: 'zoom'}).setContent("{!! $client->important_note !!}").showModal();
            @endif

            @if (str_contains(URL::previous(), 'payments'))
                $('[href="#tab-payments"]').click();
            @endif

            $('.editable-tab').click(function () {
                $('#client-edit-btn').attr('href', $(this).data('edit-link'));
            });

            let selectedTab = '#{{ $selectedTab }}' + '-tab';
            $(selectedTab).trigger('click');

            $('.create-task').click(function () {
                $('#modal-placeholder').load($(this).data('action'));
            });
            $('#client-create-note').click(function () {
                $('#note-modal-placeholder').load('{{ route('notes.create') }}');
            });
            $("#currency_code").change(function() {
                var url = '{{ route("clients.invoiceSummary", ["id" => ":id", "currency_code" => ":currency_code"]) }}';
                url = url.replace(':id', {{$client->id}});
                url = url.replace(':currency_code', $(this).val());
                $('#currency-summary').load(url);
            })
        });
    </script>
@stop

@section('content')
    <section class="content-header">
        <h1 class="pull-left">
            {{ trans('fi.view_client') }}
        </h1>
        @if(isset($typeLabels[$client->type]))
            <span class="label {{ $typeLabels[$client->type] }}" style="vertical-align: sub; margin-left: 10px; font-size: 95%;">{{ $client->name }}</span>
        @endif
        <div class="pull-right">
            @can('notes.create')
                <a href="javascript:void(0)" class="btn btn-default" id="client-create-note"><i class="fa fa-comments-o" style="padding-right:4px;"></i> {{ trans('fi.add_note') }}</a>
            @endcan
            <a href="javascript:void(0)" class="btn btn-default create-task" data-action="{{ route('task.widget.create', ['client' => $client->id]) }}"><i class="fa fa-list" style="padding-right:4px;"></i> {{ trans('fi.create_task') }}</a>
            @can('clients.update')
            <a id="client-edit-btn" href="{{ route('clients.edit', [$client->id]) }}" class="btn btn-default">{{ trans('fi.edit') }}</a>
            @endcan
            @can('clients.delete')
            <a class="btn btn-danger" href="#" data-action="{{ route('clients.delete', [$client->id]) }}" id="btn-delete-client"><i class="fa fa-trash"></i> {{ trans('fi.delete') }}</a>
            @endcan
        </div>
        <div class="clearfix"></div>
    </section>

    <section class="content">

        @if(!$client->active)
            <div class="client-inactive-watermark">{{ trans('fi.inactive') }}</div>
        @endif

        @include('layouts._alerts')

        <div class="row">

            <div class="col-xs-12">

                <div class="nav-tabs-custom">
                    <ul class="nav nav-tabs">
                        <li class="active editable-tab" data-edit-link="{{ route('clients.edit', [$client->id, 'tab' => 'general']) }}"><a id="general-tab" data-toggle="tab" href="#tab-details">{{ trans('fi.details') }}</a></li>
                        @can('quotes.view')
                        <li class="editable-tab" data-edit-link="{{ route('clients.edit', [$client->id, 'tab' => 'general']) }}"><a data-toggle="tab" href="#tab-quotes">{{ trans('fi.quotes') }}</a></li>
                        @endcan
                        @can('invoices.view')
                        <li class="editable-tab" data-edit-link="{{ route('clients.edit', [$client->id, 'tab' => 'general']) }}"><a data-toggle="tab" href="#tab-invoices">{{ trans('fi.invoices') }}</a></li>
                        @endcan
                        @can('recurring_invoices.view')
                        <li class="editable-tab" data-edit-link="{{ route('clients.edit', [$client->id, 'tab' => 'general']) }}"><a data-toggle="tab" href="#tab-recurring-invoices">{{ trans('fi.recurring_invoices') }}</a></li>
                        @endcan
                        @can('payments.view')
                        <li class="editable-tab" data-edit-link="{{ route('clients.edit', [$client->id, 'tab' => 'general']) }}"><a data-toggle="tab" href="#tab-payments">{{ trans('fi.payments') }}</a></li>
                        @endcan
                        @can('contacts.view')
                        <li class="editable-tab" data-edit-link="{{ route('clients.edit', [$client->id, 'tab' => 'contacts']) }}"><a id="contacts-tab" data-toggle="tab" href="#tab-contacts">{{ trans('fi.contacts') }} {!! $client->contacts->count() > 0 ? '<span class="label label-default">'.$client->contacts->count().'</span>' : '' !!}</a></li>
                        @endcan
                        @can('attachments.view')
                            <li class="editable-tab" data-edit-link="{{ route('clients.edit', [$client->id, 'tab' => 'attachments']) }}"><a id="attachments-tab" data-toggle="tab" href="#tab-attachments">{{ trans('fi.attachments') }} <span class="label attachment-count label-default {!! $client->attachments->count() <= 0 ? 'hide' : '' !!}">{{ $client->attachments->count() }}</span></a></li>
                        @endcan
                        @can('notes.view')
                            <li class="editable-tab" data-edit-link="{{ route('clients.edit', [$client->id, 'tab' => 'notes']) }}"><a id="notes-tab" data-toggle="tab" href="#tab-notes">{{ trans('fi.notes') }} <span class="label label-default {!! $client->notes->count() <= 0 ? 'hide' : '' !!}" id="notes-count">{{ $client->notes->count() }}</span></a></li>
                        @endcan
                        <li class="editable-tab" data-edit-link="{{ route('clients.edit', [$client->id, 'tab' => 'tasks']) }}"><a id="tasks-tab" data-toggle="tab" href="#tab-tasks">{{ trans('fi.tasks') }} <span class="label label-default {!! $client->tasks->count() <= 0 ? 'hide' : '' !!}">{{ $client->tasks->count() }}</span></a></li>
                        @if(isset($containerAddonStatus->enabled) && $containerAddonStatus->enabled == 1)
                        <li class="editable-tab" data-edit-link="{{ route('clients.edit', [$client->id, 'tab' => 'containers']) }}"><a id="containers-tab" data-toggle="tab" href="#tab-containers">{{ trans('Containers::lang.containers') }} <span class="label label-default {!! $client->containers->count() <= 0 ? 'hide' : '' !!}">{{ $client->containers->count() }}</span></a></li>
                        @endcan
                        @if(count($childClients) > 0)
                            <li class="editable-tab" data-edit-link="{{ route('clients.edit', [$client->id, 'tab' => 'childs']) }}"><a id="childs-tab" data-toggle="tab" href="#tab-childs">{{ trans('fi.child_account') }} <span class="label label-default {!! count($childClients) <= 0 ? 'hide' : '' !!}" id="child-count">{{ count($childClients) }}</span>  </a></li>
                        @endif
                            <li class="editable-tab" data-edit-link="{{ route('clients.edit', [$client->id, 'tab' => 'settings']) }}"><a id="settings-tab" data-toggle="tab" href="#tab-settings">{{ trans('fi.settings') }}</a></li>
                    </ul>
                    <div class="tab-content">

                        <div id="tab-details" class="tab-pane active">

                            <div class="row">

                                <div class="col-md-8 col-sm-12">

                                    <div class="pull-left">
                                        <h2>{!! $client->name !!}</h2>
                                        @if($client->parent_unique_name)
                                            <div style="margin-bottom:10px;font-size:15px;font-weight: bold; ">
                                                <i class="fa fa-link" aria-hidden="true"></i> <span>{{ trans('fi.parent_account') }}:</span>
                                                <a href="{{ route('clients.show', [$client->parent_client_id]) }}"><span>{!! $client->parent_unique_name !!}</span></a>
                                            </div>
                                        @endif
                                    </div>

                                </div>
                                <div class="col-md-4 col-sm-12">
                                    @if(count(array_keys($invoicePaymentSummary)) > 1)
                                        <div class="row">
                                            <div class="col-md-offset-8 col-md-3">
                                                {!! Form::select('currency', array_combine(array_keys($invoicePaymentSummary), array_keys($invoicePaymentSummary)), $client->currency_code, ['class' => 'form-control pull-right input-sm', 'id' => 'currency_code']) !!}
                                            </div>
                                        </div>
                                    @endif
                                    <div class="no-padding" id="currency-summary">
                                        @include('clients.summary', ['invoicePaymentSummary' => $invoicePaymentSummary, 'currency' => $client->currency_code])
                                    </div>

                                </div>

                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <span class="label {{ isset($typeLabels[$client->type]) ? $typeLabels[$client->type] : '' }}" style="font-size: 85%;">{{ trans('fi.' . $client->type) }}</span>
                                    <span class="label label-info" style="font-size: 85%;">{{ trans('fi.local_time') }}: {{ $client->local_time }}</span>
                                    @if(!$client->active)
                                        <span class="label label-danger text-uppercase" style="font-size: 85%;">{{ trans('fi.inactive') }}</span>
                                    @endif
                                </div>
                            </div>

                            <div class="row" style="margin-top: 5px;">

                                <div class="col-md-12">

                                    <table class="table table-striped">
                                        <tr>
                                            <td class="col-md-2"><label>{{ trans('fi.address') }}</label></td>
                                            <td class="col-md-10">{!! $client->formatted_address !!}</td>
                                        </tr>
                                        <tr>
                                            <td class="col-md-2"><label>{{ trans('fi.email') }}</label></td>
                                            <td class="col-md-10"><a href="mailto:{!! $client->email !!}">{!! $client->email !!}</a></td>
                                        </tr>
                                        <tr>
                                            <td class="col-md-2"><label>{{ trans('fi.phone') }}</label></td>
                                            <td class="col-md-10">{!! $client->phone !!}</td>
                                        </tr>
                                        <tr>
                                            <td class="col-md-2"><label>{{ trans('fi.mobile') }}</label></td>
                                            <td class="col-md-10">{!! $client->mobile !!}</td>
                                        </tr>
                                        <tr>
                                            <td class="col-md-2"><label>{{ trans('fi.fax') }}</label></td>
                                            <td class="col-md-10">{!! $client->fax !!}</td>
                                        </tr>
                                        <tr>
                                            <td class="col-md-2"><label>{{ trans('fi.web') }}</label></td>
                                            <td class="col-md-10"><a href="{!! $client->web !!}" target="_blank">{!! $client->web !!}</a></td>
                                        </tr>
                                        <tr>
                                            <td class="col-md-2"><label>{{ trans('fi.tags') }}</label></td>
                                            <td class="col-md-10">
                                                @foreach ($client->tags as $tagDetail)
                                                    <span class="label label-default" style="font-size: 85%;">{{ $tagDetail->tag->name }}</span>
                                                @endforeach
                                            </td>
                                        </tr>

                                        <tr>
                                            <td class="col-md-2"><span style="color: firebrick;background-color: pink;"> <label> Important Note:</label> </span> </td>
                                            <td class="col-md-10" style="color: firebrick;font-weight:bold;">{{ $client->important_note }}</td>
                                        </tr>

                                    </table>
                                    @if ($customFields)
                                        @include('custom_fields._custom_fields_view_unbound', ['object' => isset($client) ? $client : []])
                                    @endif
                                </div>

                            </div>

                        </div>

                        @can('quotes.view')
                        <div id="tab-quotes" class="tab-pane">
                            <div class="panel panel-default">
                                @can('quotes.create')
                                <div class="pull-right margin">
                                    <a href="javascript:void(0)" class="btn btn-sm btn-primary create-quote" data-unique-name="{{ $client->unique_name }}"><i class="fa fa-file-text-o" style="padding-right:4px;"></i>{{ trans('fi.create_quote') }}</a>
                                </div>
                                @endcan
                                @include('quotes._js_index')
                                @include('quotes._table')
                                @can('quotes.view')
                                <div class="panel-footer"><p class="text-center"><strong><a href="{{ route('quotes.index') }}?client={{ $client->id }}">{{ trans('fi.view_all') }}</a></strong></p></div>
                                @endcan
                            </div>
                        </div>
                        @endcan

                        @can('invoices.view')
                        <div id="tab-invoices" class="tab-pane">
                            <div class="panel panel-default">
                                @can('invoices.create')
                                <div class="pull-right margin">
                                    <a href="javascript:void(0)" class="btn btn-sm btn-primary create-invoice" data-unique-name="{{ $client->unique_name }}"><i class="fa fa-file-text" style="padding-right:4px;"></i>{{ trans('fi.create_invoice') }}</a>
                                </div>
                                @endcan
                                @include('invoices._js_index')
                                @include('invoices._table',['client_view' => 1])
                                <div class="panel-footer"><p class="text-center"><strong><a href="{{ route('invoices.index') }}?client={{ $client->id }}">{{ trans('fi.view_all') }}</a></strong></p></div>
                            </div>
                        </div>
                        @endcan

                        @can('recurring_invoices.view')
                        <div id="tab-recurring-invoices" class="tab-pane">
                            <div class="panel panel-default">
                                @can('recurring_invoices.create')
                                <div class="pull-right margin">
                                    <a href="javascript:void(0)" class="btn btn-sm btn-primary create-recurring-invoice" data-unique-name="{{ $client->unique_name }}"><i class="fa fa-refresh" style="padding-right:4px;"></i>{{ trans('fi.create_recurring_invoice') }}</a>
                                </div>
                                @endcan
                                @include('recurring_invoices._js_index')
                                @include('recurring_invoices._table')
                                <div class="panel-footer"><p class="text-center"><strong><a href="{{ route('recurringInvoices.index') }}?client={{ $client->id }}">{{ trans('fi.view_all') }}</a></strong></p></div>
                            </div>
                        </div>
                        @endcan

                        @can('payments.view')
                        <div id="tab-payments" class="tab-pane">
                            <div class="panel panel-default">
                                @include('payments._js_index')
                                @include('payments._table',['client_view' => 1])
                                @can('payments.view')
                                <div class="panel-footer"><p class="text-center"><strong><a href="{{ route('payments.index') }}?client={{ $client->id }}">{{ trans('fi.view_all') }}</a></strong></p></div>
                                @endcan
                            </div>
                        </div>
                        @endcan

                        @can('contacts.view')
                        <div id="tab-contacts" class="tab-pane">
                            <div class="panel panel-default">
                                @can('contacts.create')
                                <div class="pull-right margin">
                                    <a href="javascript:void(0)" class="btn btn-primary btn-sm" id="btn-add-contact"><i class="fa fa-plus"></i> {{ trans('fi.add_contact') }}</a>
                                </div>
                                @endcan
                                @include('clients._table_contacts')
                            </div>
                        </div>
                        @endcan

                        @can('attachments.view')
                        <div class="tab-pane" id="tab-attachments">
                            @include('attachments._table', ['object' => $client, 'model' => 'FI\Modules\Clients\Models\Client'])
                        </div>
                        @endcan

                        @can('notes.view')
                            <div id="tab-notes" class="tab-pane">
                                @include('notes._js_timeline', ['object' => $client, 'model' => 'FI\Modules\Clients\Models\Client', 'hideHeader' => true, 'showPrivateCheckbox' => 0])
                                <div id="note-timeline-container"></div>
                            </div>
                        @endcan

                        <div id="tab-tasks" class="tab-pane">
                            <div class="panel panel-default">
                                @include('tasks._js_index')
                                @include('tasks._table')
                            </div>
                        </div>
                        @if(isset($containerAddonStatus->enabled) && $containerAddonStatus->enabled == 1)
                            @can('containers.view')
                            <div id="tab-containers" class="tab-pane">
                                <div class="panel panel-default">
                                    @include('containers._js_index')
                                    @include('containers._table', ['object' => $client])
                                </div>
                            </div>
                            @endcan
                        @endif

                        @if(count($childClients) > 0)
                            <div id="tab-childs" class="tab-pane">
                                <div class="row" style="margin-top: 5px;">
                                    @foreach($childClients as $key => $value)
                                        <div class="col-md-12">
                                            <a href="{{ route('clients.show', [$key]) }}"><span class="label label-default">{{ $value }}</span></a>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <div id="tab-settings" class="tab-pane">

                            <div class="row" style="margin-top: 5px;">

                                <div class="col-md-12">

                                    <table class="table table-striped">
                                        <tr>
                                            <td class="col-md-2 view-field-label">{{ trans('fi.active') }}</td>
                                            <td class="col-md-10">
                                                @if($client->active == 1)
                                                    {{trans('fi.yes')}}
                                                @else
                                                    <span class="label label-danger text-uppercase">{{trans('fi.inactive')}}</span>
                                                @endif
                                            </td>
                                        </tr>

                                        <tr>
                                            <td class="col-md-2 view-field-label">{{ trans('fi.invoice_prefix') }}</td>
                                            <td class="col-md-10">{!! $client->invoice_prefix !!}</td>
                                        </tr>

                                        <tr>
                                            <td class="col-md-2 view-field-label">{{ trans('fi.automatic_email_payment_receipts') }}</td>
                                            <td class="col-md-10">{!! trans('fi.'.$client->automatic_email_payment_receipt) !!}</td>
                                        </tr>

                                        <tr>
                                            <td class="col-md-2 view-field-label">{{ trans('fi.automatic_email_on_recur') }}</td>
                                            <td class="col-md-10">{!! trans('fi.'.$client->automatic_email_on_recur) !!}</td>
                                        </tr>

                                        <tr>
                                            <td class="col-md-2 view-field-label">{{ trans('fi.default_currency') }}</td>
                                            <td class="col-md-10">{!! $client->currency_code ?? config('fi.baseCurrency') !!}</td>
                                        </tr>

                                        <tr>
                                            <td class="col-md-2 view-field-label">{{ trans('fi.language') }}</td>
                                            <td class="col-md-10">{!! $client->language ?? config('fi.language') !!}</td>
                                        </tr>

                                        <tr>
                                            <td class="col-md-2 view-field-label">{{ trans('fi.timezone') }}</td>
                                            <td class="col-md-10">{!! $client->timezone !!}</td>
                                        </tr>
                                        @if($client->parent_unique_name)
                                             <tr>
                                                <td class="col-md-2 view-field-label">{{ trans('fi.parent_account') }}</td>
                                                <td class="col-md-10"><a href="{{ route('clients.show', [$client->parent_client_id]) }}">{!! $client->parent_unique_name !!}</a></td>
                                             </tr>
                                        @endif
                                    </table>

                                </div>

                            </div>

                        </div>

                    </div>
                </div>

            </div>

        </div>
        @include('transitions.client_timeline', ['clientId'=> $client->id, 'filterUsers' => $filterUsers, 'modules' => $modules])
    </section>

@stop
