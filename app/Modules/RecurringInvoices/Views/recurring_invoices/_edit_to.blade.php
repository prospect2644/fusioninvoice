@include('recurring_invoices._js_edit_to')

<div class="box box-primary">
    <div class="box-header">
        <h3 class="box-title">{{ trans('fi.to') }}</h3>

        <div class="box-tools pull-right">
            <button class="btn btn-default btn-sm" id="btn-change-client" data-client-id="{{ $recurringInvoice->client->id }}"><i
                        class="fa fa-exchange"></i> {{ trans('fi.change') }}</button>
            <button class="btn btn-default btn-sm" id="btn-edit-client" data-client-id="{{ $recurringInvoice->client->id }}"><i
                        class="fa fa-pencil"></i> {{ trans('fi.edit') }}</button>
        </div>
    </div>
    <div class="box-body">
        @can('clients.view')
        <a href="{{ route('clients.show', [$recurringInvoice->client->id]) }}"
            title="{{ trans('fi.view_client') }}"><strong>{{ $recurringInvoice->client->unique_name }}</strong></a><br>
        @else
        <strong>{{ $recurringInvoice->client->name }}</strong><br>
        @endcan

        {!! $recurringInvoice->client->formatted_address !!}<br>
        {{ trans('fi.phone') }}: {{ $recurringInvoice->client->phone }}<br>
        {{ trans('fi.email') }}: {{ $recurringInvoice->client->email }}
    </div>
</div>