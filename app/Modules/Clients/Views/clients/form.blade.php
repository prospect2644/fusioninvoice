@extends('layouts.master')

@section('javascript')

    @include('layouts._datepicker')
    <script type="text/javascript">
        $(function() {
            $('.editable-tab').click(function(){
                $('#client-edit-form').attr('action', $(this).data('save-link'));
            });

            let selectedTab = '#{{ $selectedTab }}' + '-tab';
            $(selectedTab).trigger('click');
        });
    </script>

@stop

@section('content')

    @if ($editMode)
        {!! Form::model($client, ['route' => ['clients.update', $client->id, 'tab' => $selectedTab], 'enctype'=>'multipart/form-data', 'id' => 'client-edit-form']) !!}
    @else
        {!! Form::open(['route' => 'clients.store', 'enctype'=>'multipart/form-data']) !!}
    @endif

    <section class="content-header">
        <h1 class="pull-left">{{ trans('fi.client_form') }}</h1>

        @if ($editMode)
            <span class="label {{ $typeLabels[$client->type] }}" style="padding-bottom:.2em; vertical-align: sub; margin-left: 10px; font-size: 95%;">{{ $client->name }}</span>
        @endif

        <div class="pull-right">
            @if ($editMode)
                <a href="{{ $returnUrl }}" class="btn btn-default"><i class="fa fa-backward"></i> {{ trans('fi.back') }}</a>
            @endif
            <button class="btn btn-primary"><i class="fa fa-save"></i> {{ trans('fi.save') }}</button>
        </div>

        <div class="clearfix"></div>
    </section>

    <section class="content">

        @include('layouts._alerts')

        <div class="row">

            <div class="col-md-12">

                <div class="nav-tabs-custom">
                    <ul class="nav nav-tabs">
                        @if ($editMode)
                            <li class="active editable-tab" data-save-link="{{ route('clients.update', [$client->id, 'tab' => 'general']) }}"><a id="general-tab" href="#tab-general" data-toggle="tab">{{ trans('fi.general') }}</a></li>
                            @can('contacts.view')
                            <li class="editable-tab" data-save-link="{{ route('clients.update', [$client->id, 'tab' => 'contacts']) }}"><a id="contacts-tab" href="#tab-contacts" data-toggle="tab">{{ trans('fi.contacts') }} {!! $client->contacts->count() > 0 ? '<span class="label label-default">'.$client->contacts->count().'</span>' : '' !!}</a></li>
                            @endcan
                            @can('attachments.view')
                            <li class="editable-tab" data-save-link="{{ route('clients.update', [$client->id, 'tab' => 'attachments']) }}"><a id="attachments-tab" href="#tab-attachments" data-toggle="tab">{{ trans('fi.attachments') }} {!! $client->attachments->count() > 0 ? '<span class="label label-default">'.$client->attachments->count().'</span>' : '' !!}</a></li>
                            @endcan
                            @can('notes.view')
                            <li class="editable-tab" data-save-link="{{ route('clients.update', [$client->id, 'tab' => 'notes']) }}"><a id="notes-tab" data-toggle="tab" href="#tab-notes">{{ trans('fi.notes') }} <span class="label label-default {!! $client->notes->count() <= 0 ? 'hide' : '' !!}" id="notes-count">{{ $client->notes->count() }}</span></a></li>
                            @endcan
                            <li class="editable-tab" data-save-link="{{ route('clients.update', [$client->id, 'tab' => 'settings']) }}"><a id="settings-tab" href="#tab-settings" data-toggle="tab">{{ trans('fi.settings') }}</a></li>
                        @else
                            <li class="active"><a id="general-tab" href="#tab-general" data-toggle="tab">{{ trans('fi.general') }}</a></li>
                            <li><a id="settings-tab" href="#tab-settings" data-toggle="tab">{{ trans('fi.settings') }}</a></li>
                        @endif
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane active" id="tab-general">
                            @include('clients._form')
                        </div>
                        @if ($editMode)
                            @can('contacts.view')
                            <div class="tab-pane" id="tab-contacts">
                                @include('clients._contacts', ['contacts' => $client->contacts()->orderBy('name')->get(), 'clientId' => $client->id])
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
                        @endif
                        <div class="tab-pane" id="tab-settings">
                            @include('clients._settings')
                        </div>
                    </div>
                </div>

            </div>

        </div>

        <div class="pull-right">
            @if ($editMode)
                <a href="{{ $returnUrl }}" class="btn btn-default"><i class="fa fa-backward"></i> {{ trans('fi.back') }}</a>
            @endif
            <button class="btn btn-primary"><i class="fa fa-save"></i> {{ trans('fi.save') }}</button>
        </div>

        <div class="clearfix"></div>

    </section>

    {!! Form::close() !!}

@stop