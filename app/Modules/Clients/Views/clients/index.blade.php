@extends('layouts.master')

@section('javascript')
    @include('layouts._alertifyjs')

    <script type="text/javascript">
        $(function () {
            alertify.defaults.theme.ok = "ui negative button";
            alertify.defaults.theme.cancel = "ui black button";

            $("<style>").text(".ajs-header{ background-color: #ba0606 !important; }").appendTo($("body"));

            $('.client-filter-options').change(function () {
                $('form#filter').submit();
            });

            $('.delete-client').click(function () {
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

            $('#tags-filter-open').click(function () {
                $('#modal-placeholder').load('{!! route('clients.filterTags', ['tags' => json_encode($tags), 'tagsMustMatchAll' => $tagsMustMatchAll, 'firstLoad' => true]) !!}')
            });

            $('#btn-clear-filters').click(function () {
                $('#search').val('');
                $('#tags-filter').val('');
                $('#tags-must-match-all').val(0);
                $('.client-filter-options').prop('selectedIndex', 0);
                $('#filter').submit();
            });

            $('.create-task').click(function () {
                $('#modal-placeholder').load($(this).data('action'));
            });
        });
    </script>
@stop

@section('content')

    <section class="content-header" style="display: flex;">
        <h1 class="fa fa-users pull-left"> </h1>
        <h1 class="pull-left">{{ trans('fi.clients') }}</h1>

        <div class="btn-group" style="flex-grow: 100;">
            {!! Form::open(['method' => 'GET', 'id' => 'filter', 'class' => 'form-inline']) !!}

            <div class="pull-left" style="padding-left:20px;padding-right:30px;">
                @if (isset($searchPlaceholder))
                    <div class="input-group">
                        {!! Form::text('search', request('search'), ['id' =>'search', 'class' => 'form-control inline','autofocus','placeholder' => $searchPlaceholder]) !!}
                        <span class="input-group-btn">
                            <button type="submit" id="search-btn" class="btn btn-flat btn-primary"><i class="fa fa-search"></i></button>
                        </span>
                    </div>
                @endif
            </div>
                
            <div class="pull-right" style="padding-left:10px;padding-right:10px;">
                <button type="button" class="btn btn-default btn-flat" id="tags-filter-open"
                    data-tags="{{ json_encode($tags) }}"
                    data-match-all="{{ $tagsMustMatchAll }}"
                    >
                <span id="tags-filter-count">({{ count($tags) }})</span> {{ trans('fi.tags') }} +
                {!! Form::hidden('tags', json_encode($tags), ['id' => 'tags-filter']) !!}
                {!! Form::hidden('tagsMustMatchAll', $tagsMustMatchAll, ['id' => 'tags-must-match-all']) !!}
                </button>
                {!! Form::select('type', $types, request('type'), ['class' => 'client-filter-options form-control inline']) !!}
                {!! Form::select('status', $statuses, request('status'), ['class' => 'client-filter-options form-control inline']) !!}
            </div>

            {!! Form::close() !!}
        </div>

        <div class="pull-right" style="text-align: right;padding-left:15px;">
            @can('clients.create')
            <a href="{{ route('clients.create') }}" class="btn btn-primary btn-margin-left"><i class="fa fa-plus"></i> {{ trans('fi.new') }}</a>
            @endcan
        </div>
        
        <div class="clearfix"></div>
    </section>

    <section class="content">

        @include('layouts._alerts')

        <div class="row">

            <div class="col-xs-12">

                <div class="box box-primary">

                    <div class="box-body no-padding">
                        <table class="table table-hover table-striped">

                            <thead>
                            <tr>
                                <th class="client-table-type-indicator-column"></th>
                                <th>{!! Sortable::link('id', trans('fi.id')) !!}</th>
                                <th>{!! Sortable::link('unique_name', trans('fi.client_name')) !!}</th>
                                <th>{!! Sortable::link('email', trans('fi.email_address')) !!}</th>
                                <th>{!! Sortable::link('phone', trans('fi.phone_number')) !!}</th>
                                                                
                                <th>{!! Sortable::link('created_at', trans('fi.created')) !!}</th>

                                <th style="text-align: right;">{!! Sortable::link('balance', trans('fi.balance')) !!}</th>
                                <th>{!! Sortable::link('active', trans('fi.active')) !!}</th>
                                <th>{{ trans('fi.options') }}</th>
                            </tr>
                            </thead>

                            <tbody>
                            @foreach ($clients as $client)
                                <tr>
                                    
                                    @if($client->type=='customer') 
                                        <td class="client-table-type-indicator-column" ></td>
                                    @elseif($client->type=='lead')
                                        <td class="client-table-type-indicator-column" style="background-color: #f0ad4e;" title="Lead"></td>
                                    @elseif($client->type=='prospect')
                                        <td class="client-table-type-indicator-column" style="background-color: #dd4b39;" title="Prospect"></td>
                                    @elseif($client->type=='affiliate')
                                        <td class="client-table-type-indicator-column" style="background-color: #0080ff;" title="Affiliate"></td>                                    
                                    @else
                                        <td class="client-table-type-indicator-column" style="background-color: grey;" title="Unknown"></td>
                                    @endif 

                                    <td>{{ $client->id }}</td>
                                    
                                    @if($client->active==1) 
                                    <td><a href="{{ route('clients.show', [$client->id]) }}">{{ $client->unique_name }}</a></td>
                                    @else
                                    <td style="text-decoration: line-through;"><a href="{{ route('clients.show', [$client->id]) }}">{{ $client->unique_name }}</a></td>
                                    @endif

                                    <td>{{ $client->email }}</td>
                                    <td>{{ (($client->phone ? $client->phone : ($client->mobile ? $client->mobile : ''))) }}</td>

                                    <td>{{ $client->formatted_created_at  }}</td>

                                    <td style="text-align: right;">{{ $client->formatted_balance }}</td>
                                    <td>{{ ($client->active) ? trans('fi.yes') : trans('fi.no') }}</td>
                                    <td>
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown">
                                                {{ trans('fi.options') }} <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-right">
                                                <li><a href="{{ route('clients.show', [$client->id]) }}" id="view-client-{{ $client->id }}"><i class="fa fa-search"></i> {{ trans('fi.view') }}</a></li>
                                                @can('clients.update')
                                                <li><a href="{{ route('clients.edit', [$client->id]) }}" id="edit-client-{{ $client->id }}"><i class="fa fa-edit"></i> {{ trans('fi.edit') }}</a></li>
                                                @endcan
                                                @can('quotes.create')
                                                <li><a href="javascript:void(0)" class="create-quote" data-unique-name="{{ $client->unique_name }}"><i class="fa fa-file-text-o"></i> {{ trans('fi.create_quote') }}</a></li>
                                                @endcan
                                                @can('invoices.create')
                                                <li><a href="javascript:void(0)" class="create-invoice" data-unique-name="{{ $client->unique_name }}"><i class="fa fa-file-text"></i> {{ trans('fi.create_invoice') }}</a></li>
                                                @endcan
                                                <li><a href="javascript:void(0)" class="create-task" data-action="{{ route('task.widget.create', ['client'=>$client->id]) }}"><i class="fa fa-file-text"></i> {{ trans('fi.create_task') }}</a></li>
                                                @can('clients.delete')
                                                <li><a href="#" data-action="{{ route('clients.delete', [$client->id]) }}" id="delete-client-{{ $client->id }}" class="delete-client text-danger"><i class="fa fa-trash-o"></i> {{ trans('fi.delete') }}</a></li>
                                                @endcan
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>

                        </table>
                    </div>

                </div>

                <div class="pull-left">
                    @if(request('type') || request('status') || request('tags') || request('search'))
                        <i class="fa fa-filter"></i> {{ trans('fi.n_records_match', ['label' => $clients->total(),'plural' => $clients->total() > 1 ? 's' : '']) }}
                        <button type="button" class="btn btn-link" id="btn-clear-filters">{{ trans('fi.clear') }}</button>
                    @endif
                </div>

                <div class="pull-right">
                    {!! $clients->appends(request()->except('page'))->render() !!}
                </div>

            </div>

        </div>

    </section>

@stop