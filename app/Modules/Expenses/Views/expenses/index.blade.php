@extends('layouts.master')

@section('javascript')
    <script type="text/javascript">
        $(function () {
            $('.btn-bill-expense').click(function () {
                $('#modal-placeholder').load("{{ route('expenseBill.create') }}", {
                    id: $(this).data('expense-id'),
                    redirectTo: '{{ request()->fullUrl() }}'
                });
            });

            $('.expense_filter_options').change(function () {
                $('form#filter').submit();
            });

            alertify.defaults.theme.ok = "ui negative button";
            alertify.defaults.theme.cancel = "ui black button";

            $("<style>").text(".ajs-header{ background-color: #ba0606 !important; }").appendTo($("body"));

            $('#btn-bulk-delete').click(function () {

                var ids = [];

                $('.bulk-record:checked').each(function () {
                    ids.push($(this).data('id'));
                });

                if (ids.length > 0) {

                    alertify.confirm("{!! trans('fi.bulk_delete_record_warning') !!}", function () {
                        $.ajax({
                            url: "{{ route('expenses.bulk.delete') }}",
                            method: 'post',
                            data: {ids: ids},
                            beforeSend: function () {
                                $(".modal-loader").show();
                            },
                            success: function () {
                                $(".modal-loader").hide();
                                window.location = decodeURIComponent("{{ urlencode(request()->fullUrl()) }}");
                            }
                        });
                    }, function () {
                        alertify.alert().destroy();
                    }).setHeader(confirmHeader).set({transition: 'zoom', defaultFocus: 'cancel'});

                }
            });

            alertify.defaults.theme.ok = "ui negative button";
            alertify.defaults.theme.cancel = "ui black button";

            $("<style>").text(".ajs-header{ background-color: #ba0606 !important; }").appendTo($("body"));

            $('.delete-expenses').click(function () {

                var $_this = $(this);

                alertify.confirm("{!! trans('fi.delete_record_warning') !!}", function () {
                    window.location = decodeURIComponent($_this.data('action'));
                }, function () {
                    alertify.alert().destroy();
                }).setHeader(confirmHeader).set({transition: 'zoom', defaultFocus: 'cancel'});

            });

            $('.btn-copy-expense').click(function () {
                $.post("{{ route('expenseCopy.store') }}", {
                    expense_id: $(this).data('id')
                }).done(function (response) {
                    window.location = '{{ url('expenses') }}' + '/' + response.id + '/edit';
                }).fail(function (response) {
                    showAlertifyErrors($.parseJSON(response.responseText).errors);
                });
            });

            $('#btn-clear-filters').click(function () {
                $('#search').val('');
                $('.expense_filter_options').prop('selectedIndex', 0);
                $('#filter').submit();
            });
        });
    </script>
@stop

@section('content')

    <section class="content-header">
        <h1 class="fa fa-bank pull-left"> </h1>
        <h1 class="pull-left">
            {{ trans('fi.expenses') }}
        </h1>

        <div class="btn-group" style="flex-grow: 100;">
            {!! Form::open(['method' => 'GET', 'id' => 'filter', 'class' => 'form-inline']) !!}
            <div class="pull-left" style="padding-left:20px;">
                @if (isset($searchPlaceholder))
                    <div class="input-group">
                        {!! Form::text('search', request('search'), ['id' => 'search', 'class' => 'form-control inline input-sm','autofocus','placeholder' => $searchPlaceholder]) !!}
                        <span class="input-group-btn">
                            <button type="submit" id="search-btn" class="btn btn-flat btn-sm btn-primary"><i class="fa fa-search"></i></button>
                        </span>
                    </div>
                @endif
            </div>
            <div class="pull-left">
                @can('expenses.delete')
                <a href="javascript:void(0)" class="btn btn-danger bulk-actions btn-sm" id="btn-bulk-delete"><i class="fa fa-trash"></i> {{ trans('fi.delete') }}</a>
                @endcan
            </div>
            <div class="pull-right" style="padding-left:25px;">
                {!! Form::select('company_profile', $companyProfiles, request('company_profile'), ['class' => 'expense_filter_options form-control inline input-sm']) !!}
                {!! Form::select('status', $statuses, request('status'), ['class' => 'expense_filter_options form-control inline input-sm']) !!}
                {!! Form::select('category', $categories, request('category'), ['class' => 'expense_filter_options form-control inline input-sm']) !!}
                {!! Form::select('vendor', $vendors, request('vendor'), ['class' => 'expense_filter_options form-control inline input-sm']) !!}
            </div>
            {!! Form::close() !!}
        </div>
        <div class="pull-right" style="text-align: right;padding-left:15px;">
            @can('expenses.create')
            <a href="{{ route('expenses.create') }}" class="btn btn-primary btn-sm"><i class="fa fa-plus"></i> {{ trans('fi.new') }}</a>
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
                                @can('expenses.delete')
                                <th>
                                    <div class="btn-group"><input type="checkbox" id="bulk-select-all"></div>
                                </th>
                                @endcan
                                <th class="col-md-1">{!! Sortable::link('id', trans('fi.id')) !!}</th>
                                <th class="col-md-2">{!! Sortable::link('expense_date', trans('fi.date')) !!}</th>
                                <th class="col-md-2">{!! Sortable::link('expense_categories.name', trans('fi.category')) !!}</th>
                                <th class="col-md-3">{!! Sortable::link('description', trans('fi.description')) !!}</th>
                                <th class="col-md-1">{!! Sortable::link('amount', trans('fi.amount')) !!}</th>
                                <th class="col-md-2">{{ trans('fi.attachments') }}</th>
                                <th class="col-md-1">{{ trans('fi.options') }}</th>
                            </tr>
                            </thead>

                            <tbody>
                            @foreach ($expenses as $expense)
                                <tr>
                                    @can('expenses.delete')
                                    <td><input type="checkbox" class="bulk-record" data-id="{{ $expense->id }}"></td>
                                    @endcan
                                    <td>
                                    @can('expenses.update')
                                        <a href="{{ route('expenses.edit', [$expense->id]) }}" title="{{ trans('fi.edit') }}">{{ $expense->id }}</a></td>
                                    @else
                                        {{ $expense->id }}
                                    @endcan
                                    <td>{{ $expense->formatted_expense_date  }}</td>
                                    <td>
                                        {{ $expense->category_name }}
                                        @if ($expense->vendor_name)
                                            <br><span class="text-muted">{{ $expense->vendor_name }}</span>
                                        @endif
                                    </td>
                                    <td>{!! $expense->formatted_description !!}</td>
                                    <td>
                                        {{ $expense->formatted_amount }}
                                        @if ($expense->is_billable)
                                            @if ($expense->has_been_billed)
                                                @can('invoices.update')
                                                <br><a href="{{ route('invoices.edit', [$expense->invoice_id]) }}"><span class="label label-success">{{ trans('fi.billed') }}</span></a>
                                                @else
                                                <br><span class="label label-success">{{ trans('fi.billed') }}</span>
                                                @endcan
                                            @else
                                                <br><span class="label label-danger">{{ trans('fi.not_billed') }}</span>
                                            @endif
                                        @else
                                            <br><span class="label label-default">{{ trans('fi.not_billable') }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @foreach ($expense->attachments as $attachment)
                                            <a href="{{ $attachment->download_url }}"><i class="fa fa-file-o"></i> {{ $attachment->filename }}</a><br>
                                        @endforeach
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown">
                                                {{ trans('fi.options') }} <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-right">
                                                @can('expenses.update')
                                                @if ($expense->is_billable and !$expense->has_been_billed)
                                                    <li><a href="javascript:void(0)" class="btn-bill-expense" data-expense-id="{{ $expense->id }}"><i class="fa fa-money"></i> {{ trans('fi.bill_this_expense') }}</a></li>
                                                @endif
                                                <li><a href="{{ route('expenses.edit', [$expense->id]) }}"><i class="fa fa-edit"></i> {{ trans('fi.edit') }}</a></li>
                                                @endcan
                                                @can('expenses.create')
                                                <li><a href="#" class="btn-copy-expense" data-id="{{ $expense->id }}"><i class="fa fa-copy"></i> {{ trans('fi.copy') }}</a></li>
                                                @endcan
                                                @can('expenses.delete')
                                                <li><a href="#"
                                                       data-action="{{ route('expenses.delete',[$expense->id]) }}"
                                                       class="delete-expenses text-danger"><i
                                                                class="fa fa-trash-o"></i> {{ trans('fi.delete') }}
                                                    </a></li>
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
                    @if(request('company_profile') || request('status') || request('category') || request('vendor') || request('search'))
                        <i class="fa fa-filter"></i> {{ trans('fi.n_records_match', ['label' => $expenses->total(),'plural' => $expenses->total() > 1 ? 's' : '']) }}
                        <button type="button" class="btn btn-link" id="btn-clear-filters">{{ trans('fi.clear') }}</button>
                    @endif
                </div>

                <div class="pull-right">
                    {!! $expenses->appends(request()->except('page'))->render() !!}
                </div>

            </div>

        </div>

    </section>

@stop