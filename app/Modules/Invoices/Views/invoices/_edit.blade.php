@include('invoices._js_edit')
@include('layouts._select2')
<section class="content-header">
    <h1 class="pull-left {{($invoice->type=='credit_memo') ? 'title-credit-memo' : ''}}">
        {{ ($invoice->type == 'credit_memo') ? trans('fi.credit_memo') : trans('fi.invoice') }}
        #{{ $invoice->number }}</h1>

    @if ($invoice->viewed)
        <span style="margin-left: 10px;" class="label label-success">{{ trans('fi.viewed') }}</span>
    @else
        <span style="margin-left: 10px;" class="label label-default">{{ trans('fi.not_viewed') }}</span>
    @endif

    @if ($invoice->quote()->count())
        @can('quotes.update')
        <span class="label label-info">
            <a href="{{ route('quotes.edit', [$invoice->quote->id]) }}"
               style="color: inherit;">{{ trans('fi.converted_from_quote') }} {{ $invoice->quote->number }}</a>
        </span>
        @endcan
    @endif

    @if ($invoice->recurring_invoice_id > 0)
        <span style="margin-left: 10px;">{{ trans('fi.created_recurring_invoice_id') }}:
            <a href="{{ route('recurringInvoices.edit', $invoice->recurring_invoice_id) }}">{{ $invoice->recurring_invoice_id }}</a>
        </span>
        {!! Form::hidden('number', $invoice->recurring_invoice_id, ['recurring_invoice_id' => 'number']) !!}
    @endif

    <div class="pull-right">


        <div class="btn-group">
            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                {{ trans('fi.send-to') }} <span class="caret"></span>
            </button>
            <ul class="dropdown-menu dropdown-menu-right" role="menu">
                <li>
                    <a href="{{ route('invoices.pdf', [$invoice->id]) }}" target="_blank" id="btn-pdf-invoice"><i
                                class="fa fa-file-pdf-o"></i> {{ trans('fi.pdf') }}</a>
                </li>
                <li>
                    <a href="javascript:void(0);" data-action="{{ route('invoices.save.pdf', [$invoice->id]) }}" id="btn-print-invoice"><i
                                class="fa fa-print"></i> {{ trans('fi.print') }}</a>
                </li>
                @if (config('fi.mailConfigured'))
                    <li>
                        <a href="javascript:void(0)" id="btn-email-invoice" class="email-invoice"
                           data-invoice-id="{{ $invoice->id }}"
                           data-redirect-to="{{ route('invoices.edit', [$invoice->id]) }}">
                            <i class="fa fa-envelope"></i> {{ trans('fi.email') }}</a>
                    </li>
                @endif
            </ul>
        </div>

        <div class="btn-group">
            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                {{ trans('fi.other') }} <span class="caret"></span>
            </button>
            <ul class="dropdown-menu dropdown-menu-right" role="menu">
                @can('payments.create')
                @if ($invoice->isPayable)
                    <li>
                        <a href="javascript:void(0)" id="btn-enter-payment" class="enter-payment"
                           data-invoice-id="{{ $invoice->id }}"
                           data-invoice-balance="{{ $invoice->amount->formatted_numeric_balance }}"
                           data-redirect-to="{{ route('invoices.edit', [$invoice->id]) }}"><i
                                    class="fa fa-credit-card">
                            </i> {{ trans('fi.enter_payment') }}
                        </a>
                    </li>
                    @if($creditMemoCount > 0)
                        <li>
                            <a href="javascript:void(0)" id="btn-apply-credit-memo" class="apply-credit-memo"
                               data-invoice-id="{{ $invoice->id }}"
                               data-invoice-balance="{{ $invoice->amount->formatted_numeric_balance }}"
                               data-redirect-to="{{ route('invoices.edit', [$invoice->id]) }}"><i
                                        class="fa fa-list-alt">
                                </i> {{ trans('fi.apply_credit_memo') }}
                            </a>
                        </li>
                    @endif
                    @if($prePaymentCount > 0)
                        <li>
                            <a href="javascript:void(0)" id="btn-apply-pre-payment" class="apply-pre-payment"
                               data-invoice-id="{{ $invoice->id }}"
                               data-invoice-balance="{{ $invoice->amount->formatted_numeric_balance }}"
                               data-redirect-to="{{ route('invoices.edit', [$invoice->id]) }}"><i
                                        class="fa fa-money">
                                </i> {{ trans('fi.apply_pre_payment') }}
                            </a>
                        </li>
                    @endif
                @elseif($invoice->type == 'credit_memo' && abs($invoice->amount->balance) > 0 && $invoiceCount > 0)
                    <li>
                        <a href="javascript:void(0)" id="btn-apply-to-invoices" class="apply-to-invoices"
                           data-invoice-id="{{ $invoice->id }}"
                           data-invoice-balance="{{ $invoice->amount->formatted_numeric_balance }}"
                           data-redirect-to="{{ route('invoices.edit', [$invoice->id]) }}"><i
                                    class="fa fa-hand-o-right">
                            </i> {{ trans('fi.apply_to_invoices') }}
                        </a>
                    </li>
                @endif
                @endcan
                <li><a href="javascript:void(0)" id="btn-copy-invoice"><i
                                class="fa fa-copy"></i> {{ trans('fi.copy') }}</a></li>
                <li><a href="{{ route('clientCenter.public.invoice.show', [$invoice->url_key]) }}" target="_blank"><i
                                class="fa fa-globe"></i> {{ trans('fi.public') }}</a></li>
                <li class="divider"></li>
                @can('invoices.delete')
                <li><a href="#" class="btn-delete-invoice text-danger"><i
                                class="fa fa-trash-o"></i> {{ trans('fi.delete') }}</a></li>
                @endcan
            </ul>
        </div>


        @if ($returnUrl)
            <a href="{{ $returnUrl }}" class="btn btn-default"><i
                        class="fa fa-backward"></i> {{ trans('fi.back') }}</a>
        @endif


        <div class="btn-group">
            <button type="button" class="btn btn-primary btn-save-invoice"><i
                        class="fa fa-save"></i> {{ trans('fi.save') }}</button>
            <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown">
                <span class="caret"></span>
            </button>
            <ul class="dropdown-menu dropdown-menu-right" role="menu">
                <li><a href="#" class="btn-save-invoice"
                       data-apply-exchange-rate="1">{{ trans('fi.save_and_apply_exchange_rate') }}</a></li>
            </ul>
        </div>

    </div>

    <div class="clearfix"></div>
</section>

<section class="content">

    <div class="row">

        <div class="col-lg-10">

            <div id="form-status-placeholder"></div>

            <div class="box box-primary">
                <!-- /.box-header -->
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label>
                                    <div class="box-header" style="padding: 0 0 5px 0">
                                        <h3 class="box-title">{{ trans('fi.summary') }}</h3>
                                    </div>
                                </label>
                                {!! Form::text('summary', $invoice->summary, ['id' => 'summary', 'class' => 'form-control']) !!}
                            </div>
                        </div>
                        <!-- /.col -->
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>
                                    <div class="box-header" style="padding: 0 0 5px 0">
                                        <h3 class="box-title">{{ trans('fi.tags') }}</h3>
                                    </div>
                                </label>
                                {!! Form::select('tags[]', $tags, $selectedTags, ['class' => 'form-control client-tags','multiple' => true, 'id' => 'invoice-tags', 'style' => 'width:100%']) !!}
                            </div>
                        </div>
                    </div>
                    <!-- /.row -->
                </div>
                <!-- /.box-body -->
            </div>

            <div class="row">

                <div class="col-sm-6" id="col-from">

                    @include('invoices._edit_from')

                </div>

                <div class="col-sm-6" id="col-to">

                    @include('invoices._edit_to')

                </div>

            </div>

            <div class="row">

                <div class="col-sm-12 table-responsive" style="overflow-x: visible;">
                    <div class="box box-primary">
                        <div class="box-header">
                            <h3 class="box-title">{{ trans('fi.items') }}</h3>

                            <div class="box-tools pull-right">
                                <button class="btn btn-primary btn-sm" id="btn-add-item"><i
                                            class="fa fa-plus"></i> {{ trans('fi.add_item') }}</button>
                            </div>
                        </div>

                        <div class="box-body">
                            <table id="item-table" class="table table-hover table-striped sortable-item">
                                <thead>
                                <tr>
                                    <th style="width: 2%"></th>
                                    <th style="width: 20%;">{{ trans('fi.product') }}</th>
                                    <th style="width: 25%;">{{ trans('fi.description') }}</th>
                                    <th style="width: 10%;">{{ trans('fi.qty') }}</th>
                                    <th style="width: 10%;">{{ trans('fi.price') }}</th>
                                    <th style="width: 10%;">{{ trans('fi.tax_1') }}</th>
                                    @if(config('fi.numberOfTaxFields') == '2')
                                        <th style="width: 10%;">{{ trans('fi.tax_2') }}</th>
                                    @endif
                                    <th style="width: 10%; text-align: right; padding-right: 25px;">{{ trans('fi.total') }}</th>
                                    <th style="width: 5%;"></th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr id="new-item" style="display: none;">
                                    <td class="handle"><i class="fa fa-sort"></i></td>
                                    <td>
                                        {!! Form::hidden('invoice_id', $invoice->id) !!}
                                        {!! Form::hidden('id', '') !!}
                                        {!! itemLookUpsDropDown() !!}<br>
                                        <label class="lbl_item_lookup">
                                            <input type="checkbox" class="update_item_lookup" name="save_item_as_lookup"
                                                   tabindex="999"> {{ trans('fi.save_item_as_lookup') }}
                                        </label>
                                    </td>
                                    <td>{!! Form::textarea('description', null, ['class' => 'form-control input-sm', 'rows' => 1]) !!}</td>
                                    <td>{!! Form::text('quantity', null, ['class' => 'form-control input-sm']) !!}</td>
                                    <td>{!! Form::text('price', null, ['class' => 'form-control input-sm']) !!}</td>
                                    <td>{!! Form::select('tax_rate_id', $taxRates, config('fi.itemTaxRate'), ['class' => 'form-control input-sm']) !!}</td>
                                    @if(config('fi.numberOfTaxFields') == '2')
                                        <td>{!! Form::select('tax_rate_2_id', $taxRates, config('fi.itemTax2Rate'), ['class' => 'form-control input-sm']) !!}</td>
                                    @endif
                                    <td></td>
                                    <td>
                                        <a class="btn btn-xs btn-danger btn-delete-invoice-item"
                                           href="javascript:void(0);"
                                           title="{{ trans('fi.delete') }}">
                                            <i class="fa fa-times"></i>
                                        </a>
                                    </td>
                                </tr>
                                @foreach ($invoice->items as $item)
                                    <tr class="item" id="tr-item-{{ $item->id }}">
                                        <td class="handle"><i class="fa fa-sort"></i></td>
                                        <td>
                                            {!! Form::hidden('invoice_id', $invoice->id) !!}
                                            {!! Form::hidden('id', $item->id) !!}
                                            {!! itemLookUpsDropDown($item, 'item-lookup') !!}
                                            <label class="lbl_item_lookup" style="display: none;">
                                                <input type="checkbox" class="update_item_lookup"
                                                       name="save_item_as_lookup"
                                                       tabindex="999"> {{ trans('fi.save_item_as_lookup') }}
                                            </label>
                                        </td>
                                        <td>{!! Form::textarea('description', $item->description, ['class' => 'form-control input-sm', 'rows' => 1]) !!}</td>
                                        <td>{!! Form::text('quantity', $item->formatted_quantity, ['class' => 'form-control input-sm']) !!}</td>
                                        <td>{!! Form::text('price', $item->formatted_numeric_price, ['class' => 'form-control input-sm']) !!}</td>
                                        <td>{!! Form::select('tax_rate_id', $taxRates, $item->tax_rate_id, ['class' => 'form-control input-sm']) !!}</td>
                                        @if(config('fi.numberOfTaxFields') == '2')
                                            <td>{!! Form::select('tax_rate_2_id', $taxRates, $item->tax_rate_2_id, ['class' => 'form-control input-sm']) !!}</td>
                                        @endif
                                        <td style="text-align: right; padding-right: 25px;">{{ $item->amount->formatted_subtotal }}</td>
                                        <td>
                                            <a class="btn btn-xs btn-danger btn-delete-invoice-item"
                                               href="javascript:void(0);"
                                               title="{{ trans('fi.delete') }}" data-item-id="{{ $item->id }}">
                                                <i class="fa fa-times"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>

            </div>

            <div class="row">

                <div class="col-lg-12">
                    <div class="nav-tabs-custom">
                        <ul class="nav nav-tabs">
                            <li class="active"><a href="#tab-additional"
                                                  data-toggle="tab">{{ trans('fi.additional') }}</a></li>
                            @can('notes.view')
                            <li><a href="#tab-notes" data-toggle="tab">{{ trans('fi.notes') }} <span
                                            class="label label-default {!! $invoice->notes->count() <= 0 ? 'hide' : '' !!}"
                                            id="notes-count">{{ $invoice->notes->count() }}</span></a>
                            </li>
                            @endcan
                            @can('attachments.view')
                            <li><a href="#tab-attachments"
                                   data-toggle="tab">{{ trans('fi.attachments') }} {!! $invoice->attachments->count() > 0 ? '<span class="label label-default">'.$invoice->attachments->count().'</span>' : '' !!}</a>
                            </li>
                            @endcan
                            @if($invoice->type == 'credit_memo')
                                @can('invoices.view')
                                <li><a href="#tab-credit-applications" data-toggle="tab">
                                        {{ trans('fi.credit_applications') }}
                                        {!! (($invoice->getCreditApplication()->count()) > 0) ? '<span class="label credit-application-count label-default">'.$invoice->getCreditApplication()->count().'</span>' : '' !!}
                                    </a>
                                </li>
                                @endcan
                            @else
                                @can('payments.view')
                                <li><a href="#tab-payments"
                                       data-toggle="tab">{{ trans('fi.payments') }} {!! $invoice->payments->count() > 0 ? '<span class="label paymentcount label-default">'.$invoice->payments->count().'</span>' : '' !!}</a>
                                </li>
                                @endcan
                            @endif
                        </ul>
                        <div class="tab-content">

                            <div class="tab-pane active" id="tab-additional">
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{ trans('fi.terms_and_conditions') }}</label>
                                            {!! Form::textarea('terms', $invoice->terms, ['id' => 'terms', 'class' => 'form-control', 'rows' => 5]) !!}
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{ trans('fi.footer') }}</label>
                                            {!! Form::textarea('footer', $invoice->footer, ['id' => 'footer', 'class' => 'form-control', 'rows' => 5]) !!}
                                        </div>
                                    </div>
                                </div>

                                @if ($customFields)
                                    <div class="row">
                                        <div class="col-md-12">
                                            @include('custom_fields._custom_fields_unbound', ['object' => $invoice])
                                        </div>
                                    </div>
                                @endif
                            </div>

                            @can('notes.view')
                            <div class="tab-pane" id="tab-notes">
                                <div class="row">
                                    <div class="col-lg-12">
                                        @include('notes._js_timeline', ['object' => $invoice, 'model' => 'FI\Modules\Invoices\Models\Invoice', 'hideHeader' => true, 'showPrivateCheckbox' => 1])
                                        <div id="note-timeline-container"></div>
                                    </div>
                                </div>
                            </div>
                            @endcan

                            @can('attachments.view')
                            <div class="tab-pane" id="tab-attachments">
                                <div class="row">
                                    <div class="col-lg-12">
                                        @include('attachments._table', ['object' => $invoice, 'model' => 'FI\Modules\Invoices\Models\Invoice'])
                                    </div>
                                </div>
                            </div>
                            @endcan

                            @can('payments.view')
                            @if($invoice->type == 'invoice')
                                <div class="tab-pane" id="tab-payments">
                                    @include('invoices._payments', ['payments' => $invoice->payments, 'invoiceId' => $invoice->id])
                                </div>
                            @elseif($invoice->type == 'credit_memo')
                                <div class="tab-pane" id="tab-credit-applications">
                                    @include('invoices._credit_applications', ['creditApplications' => $invoice->getCreditApplication(), 'creditMemoId' => $invoice->id])
                                </div>
                            @endif
                            @endcan
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-2">

            <div id="div-totals">
                @include('invoices._edit_totals')
            </div>

            <div class="box box-primary">

                <div class="box-header">
                    <h3 class="box-title">{{ trans('fi.options') }}</h3>
                </div>

                <div class="box-body">

                    <div class="form-group">
                        <label>{{ trans('fi.invoice') }} #</label>
                        {!! Form::text('number', $invoice->number, ['id' => 'number', 'class' =>
                        'form-control
                        input-sm']) !!}
                    </div>

                    <div class="form-group">
                        <label>{{ trans('fi.date') }}</label>
                        {!! Form::text('invoice_date', $invoice->formatted_invoice_date, ['id' =>
                        'invoice_date', 'class' => 'form-control input-sm']) !!}
                    </div>

                    <div class="form-group">
                        <label>{{ trans('fi.due_date') }}</label>
                        {!! Form::text('due_at', $invoice->formatted_due_at, ['id' => 'due_at', 'class'
                        => 'form-control input-sm']) !!}
                    </div>

                    <div class="form-group">
                        <label>{{ trans('fi.discount') }}</label>

                        <div class="input-group">
                            {!! Form::text('discount', $invoice->formatted_numeric_discount, ['id' =>
                            'discount', 'class' => 'form-control input-sm']) !!}
                            <span class="input-group-addon">%</span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>{{ trans('fi.currency') }}</label>
                        {!! Form::select('currency_code', $currencies, $invoice->currency_code, ['disabled' => (($invoice->amount->paid == 0) ? false : true), 'id' =>
                        'currency_code', 'class' => 'form-control input-sm', 'style' => config('fi.baseCurrency') != $invoice->currency_code ? 'background:#fff8dc' : '']) !!}
                    </div>

                    <div class="form-group">
                        <label>{{ trans('fi.exchange_rate') }}</label>

                        <div class="input-group">
                            {!! Form::text('exchange_rate', $invoice->exchange_rate, ['id' =>
                            'exchange_rate', 'disabled' => (($invoice->amount->paid == 0) ? false : true), 'class' => 'form-control input-sm', 'style' => config('fi.baseCurrency') != $invoice->currency_code ? 'background:#fff8dc' : '']) !!}
                            <span class="input-group-btn">
                                <button
                                        {{(($invoice->amount->paid == 0) ? '' : 'disabled')}}
                                        class="btn btn-default btn-sm" id="btn-update-exchange-rate" type="button"
                                        data-toggle="tooltip" data-placement="left"
                                        title="{{ trans('fi.update_exchange_rate') }}"><i class="fa fa-refresh"></i>
                                </button>
                            </span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>{{ trans('fi.status') }}</label>
                        {!! Form::select('status', $statuses, $invoice->status,
                        ['id' => 'status', 'class' => 'form-control input-sm']) !!}
                    </div>

                    <div class="form-group">
                        <label>{{ trans('fi.template') }}</label>
                        {!! Form::select('template', $templates, $invoice->template,
                        ['id' => 'template', 'class' => 'form-control input-sm']) !!}
                    </div>

                </div>
            </div>
        </div>
    </div>

    <div class="pull-right">
        <div class="btn-group">
            @if ($returnUrl)
                <a href="{{ $returnUrl }}" class="btn btn-default"><i
                            class="fa fa-backward"></i> {{ trans('fi.back') }}</a>
            @endif
        </div>
        <div class="btn-group">
            <button type="button" class="btn btn-primary btn-save-invoice"><i
                        class="fa fa-save"></i> {{ trans('fi.save') }}</button>
            <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown">
                <span class="caret"></span>
            </button>
            <ul class="dropdown-menu dropdown-menu-right" role="menu">
                <li><a href="#" class="btn-save-invoice"
                       data-apply-exchange-rate="1">{{ trans('fi.save_and_apply_exchange_rate') }}</a></li>
            </ul>
        </div>
    </div>
    <div><br><br><br><br><br></div>
    <div class="clearfix"></div>
</section>
