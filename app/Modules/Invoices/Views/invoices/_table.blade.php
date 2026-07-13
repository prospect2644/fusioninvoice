<table class="table table-hover table-striped">

    <thead>
    <tr>
        @if(isset($bulk_action) && $bulk_action == true)
            <th>
                <div class="btn-group"><input type="checkbox" id="bulk-select-all"></div>
            </th>
        @endif
        <th class="hidden-sm hidden-xs">{{ trans('fi.status') }}</th>
        <th>{!! Sortable::link('number', trans('fi.invoice'), 'invoices') !!}</th>
        {{--Recurred from Invoice--}}
        <th>{{ trans('fi.recurring_id') }}</th>
        <th class="hidden-xs">{!! Sortable::link('invoice_date', trans('fi.date'), 'invoices') !!}</th>
        <th class="hidden-md hidden-sm hidden-xs">{!! Sortable::link('due_at', trans('fi.due'), 'invoices') !!}</th>
        @if(!isset($client_view))
            <th>{!! Sortable::link('clients.name', trans('fi.client'), 'invoices') !!}</th>
        @endif
        <th class="hidden-sm hidden-xs">{!! Sortable::link('summary', trans('fi.summary'), 'invoices') !!}</th>
        <th style="text-align: center;" class="hidden-sm hidden-xs">{{trans('fi.tags')}}</th>
        <th style="text-align: center;">{!! Sortable::link('invoice_amounts.total', trans('fi.total'), 'invoices') !!}</th>
        <th style="text-align: center;" class="hidden-sm hidden-xs">
            {!! Sortable::link('invoice_amounts.balance', trans('fi.balance'), 'invoices') !!}
        </th>
        <th style="text-align: center;">{{ trans('fi.options') }}</th>
    </tr>
    </thead>

    <tbody>
    @foreach ($invoices as $invoice)
        <tr>

            @if(isset($bulk_action) && $bulk_action == true)
                <td class="{{($invoice->type=='credit_memo') ? 'column-credit-memo' : ''}}">
                    <input type="checkbox" class="bulk-record" data-id="{{ $invoice->id }}">
                </td>
            @endif
            <td class="hidden-sm hidden-xs {{(isset($client_view) && $invoice->type=='credit_memo') ? 'column-credit-memo' : ''}}">
                <span class="label label-{{ $invoice->status }}">{{ trans('fi.' . $invoice->status) }}</span>
                @if ($invoice->viewed)
                    <span class="label label-success">{{ trans('fi.viewed') }}</span>
                @else
                    <span class="label label-default">{{ trans('fi.not_viewed') }}</span>
                @endif
            </td>
            <td>
                @can('invoices.update')
                <a href="{{ route('invoices.edit', [$invoice->id]) }}"
                   title="{{ trans('fi.edit') }}">{{ $invoice->number }}</a>
                @else
                    {{ $invoice->number }}
                    @endcan
            </td>

            {{--Only displays if the invoice was created from a recurring.--}}
            @if ($invoice->recurring_invoice_id > 0)
                <td class="hidden-xs">{{ $invoice->recurring_invoice_id }}</td>
            @else
                <td class="hidden-xs">{{''}}</td>
            @endif

            <td class="hidden-xs">{{ $invoice->formatted_invoice_date }}</td>
            <td class="hidden-md hidden-sm hidden-xs"
                @if ($invoice->isOverdue) style="color: red; font-weight: bold;" @endif>{{ $invoice->formatted_due_at }}</td>
            @if(!isset($client_view))
                <td><a href="{{ route('clients.show', [$invoice->client->id]) }}"
                       title="{{ trans('fi.view_client') }}">{{ $invoice->client->unique_name }}</a></td>
            @endif
            <td class="hidden-sm hidden-xs">{{ $invoice->short_summary }}</td>
            <td class="hidden-sm hidden-xs">{{ $invoice->formatted_tags }}</td>
            <td style="text-align: right; padding-right: 25px;">{{ $invoice->amount->formatted_total }}</td>
            <td class="hidden-sm hidden-xs"
                style="text-align: right; padding-right: 25px;">{{ $invoice->amount->formatted_balance }}</td>
            <td align="center">
                <div class="btn-group">
                    <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown">
                        {{ trans('fi.options') }} <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-right">
                        @can('invoices.update')
                        <li><a href="{{ route('invoices.edit', [$invoice->id]) }}"><i
                                        class="fa fa-edit"></i> {{ trans('fi.edit') }}</a></li>
                        @endcan
                        <li><a href="{{ route('invoices.pdf', [$invoice->id]) }}" target="_blank"
                               id="btn-pdf-invoice"><i class="fa fa-file-pdf-o"></i> {{ trans('fi.pdf') }}</a></li>
                        <li><a href="javascript:void(0);" data-action="{{ route('invoices.save.pdf', [$invoice->id]) }}"
                               class="btn-print-invoice"><i class="fa fa-print"></i> {{ trans('fi.print') }}</a></li>
                        <li><a href="javascript:void(0)" class="email-invoice" data-invoice-id="{{ $invoice->id }}"
                               data-redirect-to="{{ request()->fullUrl() }}"><i
                                        class="fa fa-envelope"></i> {{ trans('fi.email') }}</a></li>
                        <li><a href="{{ route('clientCenter.public.invoice.show', [$invoice->url_key]) }}"
                               target="_blank" id="btn-public-invoice"><i
                                        class="fa fa-globe"></i> {{ trans('fi.public') }}</a></li>
                        @can('invoices.create')
                        <li>
                            <a href="javascript:void(0)" class="btn-copy-invoice" data-invoice-id="{{ $invoice->id }}">
                                <i class="fa fa-copy"></i> {{ trans('fi.copy') }}
                            </a>
                        </li>
                        @endcan
                        @can('payments.create')
                        @if ($invoice->isPayable)
                            <li><a href="javascript:void(0)" id="btn-enter-payment" class="enter-payment"
                                   data-invoice-id="{{ $invoice->id }}"
                                   data-invoice-balance="{{ $invoice->amount->formatted_numeric_balance }}"
                                   data-redirect-to="{{ request()->fullUrl() }}"><i
                                            class="fa fa-credit-card"></i> {{ trans('fi.enter_payment') }}</a></li>
                            @if(($invoice->count_credit_memo > 0) && ($invoice->type != 'credit_memo'))
                                <li>
                                    <a href="javascript:void(0)" id="btn-apply-credit-memo"
                                       class="apply-credit-memo"
                                       data-invoice-id="{{ $invoice->id }}"
                                       data-invoice-balance="{{ $invoice->amount->formatted_numeric_balance }}"
                                       data-settlement-type="credit_memo"
                                       data-redirect-to="{{ request()->fullUrl() }}"><i
                                                class="fa fa-list-alt">
                                        </i> {{ trans('fi.apply_credit_memo') }}
                                    </a>
                                </li>
                            @endif
                            @if(($invoice->count_pre_payment > 0) && ($invoice->type != 'credit_memo'))
                                <li>
                                    <a href="javascript:void(0)" id="btn-apply-pre-payment"
                                       class="apply-pre-payment"
                                       data-invoice-id="{{ $invoice->id }}"
                                       data-settlement-type="pre_payment"
                                       data-invoice-balance="{{ $invoice->amount->formatted_numeric_balance }}"
                                       data-redirect-to="{{ request()->fullUrl() }}"><i
                                                class="fa fa-money">
                                        </i> {{ trans('fi.apply_pre_payment') }}
                                    </a>
                                </li>
                            @endif
                        @endif
                        @if ($invoice->isAppliable and $invoice->count_sent_invoices > 0)
                            <li>
                                <a href="javascript:void(0)" id="btn-apply-to-invoices" class="apply-to-invoices"
                                   data-invoice-id="{{ $invoice->id }}"
                                   data-invoice-balance="{{ $invoice->amount->formatted_numeric_balance }}"
                                   data-redirect-to="{{ request()->fullUrl() }}"><i
                                            class="fa fa-hand-o-right">
                                    </i> {{ trans('fi.apply_to_invoices') }}
                                </a>
                            </li>
                        @endif
                        @endcan
                        @can('invoices.delete')
                        <li>
                            <a href="#" data-action="{{ route('invoices.delete', [$invoice->id]) }}"
                               class="delete-invoice text-danger">
                                <i class="fa fa-trash-o"></i> {{ trans('fi.delete') }}
                            </a>
                        </li>
                        @endcan
                    </ul>
                </div>
            </td>
        </tr>
    @endforeach
    </tbody>

</table>
