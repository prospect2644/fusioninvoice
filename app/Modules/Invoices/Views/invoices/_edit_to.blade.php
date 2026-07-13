@include('invoices._js_edit_to')

<div class="box box-primary">
    <div class="box-header">
        <h3 class="box-title">{{ trans('fi.to') }}</h3>

        <div class="box-tools pull-right">
            @can('clients.view')
            <button class="btn btn-default btn-sm" id="btn-change-client" data-client-id="{{ $invoice->client->id }}"><i
                        class="fa fa-exchange"></i> {{ trans('fi.change') }}</button>
            @endcan
            @can('clients.update')
            <button class="btn btn-default btn-sm" id="btn-edit-client" data-client-id="{{ $invoice->client->id }}"><i
                        class="fa fa-pencil"></i> {{ trans('fi.edit') }}</button>
            @endcan
        </div>
    </div>
    <div class="box-body">
        @can('clients.view')
        <a href="{{ route('clients.show', [$invoice->client->id]) }}"
            title="{{ trans('fi.view_client') }}"><strong>{{ $invoice->client->unique_name }}</strong></a><br>
        @else
        <strong>{{ $invoice->client->name }}</strong><br>
        @endcan

        {!! $invoice->client->formatted_address !!}<br>
        {{ trans('fi.phone') }}: {{ $invoice->client->phone }}<br>
        {{ trans('fi.email') }}: {{ $invoice->client->email }}
    </div>
</div>