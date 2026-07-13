@include('quotes._js_edit')

<section class="content-header">
    <h1 class="pull-left">{{ trans('fi.quote') }} #{{ $quote->number }}</h1>

    @if ($quote->viewed)
        <span style="margin-left: 10px;" class="label label-success">{{ trans('fi.viewed') }}</span>
    @else
        <span style="margin-left: 10px;" class="label label-default">{{ trans('fi.not_viewed') }}</span>
    @endif

    @if ($quote->invoice)
        <span class="label label-info">
            <a href="{{ route('invoices.edit', [$quote->invoice_id]) }}"
               style="color: inherit;">{{ trans('fi.converted_to_invoice') }} {{ $quote->invoice->number }}</a>
        </span>
    @endif

    <div class="pull-right">
        <div class="btn-group">
            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                {{ trans('fi.send-to') }} <span class="caret"></span>
            </button>
            <ul class="dropdown-menu dropdown-menu-right" role="menu">
                <li>
                    <a href="{{ route('quotes.pdf', [$quote->id]) }}" target="_blank" id="btn-pdf-quote"><i class="fa fa-file-pdf-o"></i> {{ trans('fi.pdf') }}</a>
                </li>
                <li>
                    <a href="javascript:void(0);" data-action="{{ route('quotes.save.pdf', [$quote->id]) }}" id="btn-print-quote"><i
                                class="fa fa-print"></i> {{ trans('fi.print') }}</a>
                </li>
                @if (config('fi.mailConfigured'))
                    @can('quotes.update')
                    <li>
                        <a href="javascript:void(0)" id="btn-email-quote" class="email-quote"
                           data-quote-id="{{ $quote->id }}" data-redirect-to="{{ route('quotes.edit', [$quote->id]) }}"><i
                                    class="fa fa-envelope"></i> {{ trans('fi.email') }}</a></li>
                    @endcan
                @endif
            </ul>
        </div>
        <div class="btn-group">
            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                {{ trans('fi.other') }} <span class="caret"></span>
            </button>
            <ul class="dropdown-menu dropdown-menu-right" role="menu">
                <li><a href="javascript:void(0)" id="btn-copy-quote"><i
                                class="fa fa-copy"></i> {{ trans('fi.copy') }}</a></li>
                <li><a href="javascript:void(0)" id="btn-quote-to-invoice"><i
                                class="fa fa-check"></i> {{ trans('fi.quote_to_invoice') }}</a></li>
                <li><a href="{{ route('clientCenter.public.quote.show', [$quote->url_key]) }}" target="_blank"><i
                                class="fa fa-globe"></i> {{ trans('fi.public') }}</a></li>
                <li class="divider"></li>
                @can('quotes.delete')
                <li><a class="btn-delete-quote text-danger" href="#"><i
                                class="fa fa-trash-o"></i> {{ trans('fi.delete') }}</a></li>
                @endcan
            </ul>
        </div>

        <div class="btn-group">
            @if ($returnUrl)
                <a href="{{ $returnUrl }}" class="btn btn-default"><i
                            class="fa fa-backward"></i> {{ trans('fi.back') }}</a>
            @endif
        </div>

        <div class="btn-group">
            <button type="button" class="btn btn-primary btn-save-quote"><i
                        class="fa fa-save"></i> {{ trans('fi.save') }}</button>
            <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown">
                <span class="caret"></span>
            </button>
            <ul class="dropdown-menu dropdown-menu-right" role="menu">
                <li><a href="#" class="btn-save-quote"
                       data-apply-exchange-rate="1">{{ trans('fi.save_and_apply_exchange_rate') }}</a></li>
            </ul>
        </div>

    </div>

    <div class="clearfix"></div>
</section>

<section class="content">

    <div class="row">

        <div class="col-lg-10">

            @include('layouts._alerts')

            <div id="form-status-placeholder"></div>

            <div class="row">
                <div class="col-md-12">
                    <div class="box box-primary">
                        <div class="box-header">
                            <h3 class="box-title">{{ trans('fi.summary') }}</h3>
                        </div>
                        <div class="box-body">
                            {!! Form::text('summary', $quote->summary, ['id' => 'summary', 'class' => 'form-control']) !!}
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">

                <div class="col-sm-6" id="col-from">

                    @include('quotes._edit_from')

                </div>

                <div class="col-sm-6" id="col-to">

                    @include('quotes._edit_to')

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
                                        {!! Form::hidden('quote_id', $quote->id) !!}
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
                                        <a class="btn btn-xs btn-danger btn-delete-quote-item"
                                           href="javascript:void(0);"
                                           title="{{ trans('fi.delete') }}">
                                            <i class="fa fa-times"></i>
                                        </a>
                                    </td>
                                </tr>
                                @foreach ($quote->items as $item)
                                    <tr class="item" id="tr-item-{{ $item->id }}">
                                        <td class="handle"><i class="fa fa-sort"></i></td>
                                        <td>
                                            {!! Form::hidden('quote_id', $quote->id) !!}
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
                                            <a class="btn btn-xs btn-danger btn-delete-quote-item"
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
                                            class="label label-default {!! $quote->notes->count() <= 0 ? 'hide' : '' !!}"
                                            id="notes-count">{{ $quote->notes->count() }}</span></a></li>
                            @endcan
                            @can('attachments.view')
                            <li><a href="#tab-attachments"
                                   data-toggle="tab">{{ trans('fi.attachments') }} {!! $quote->attachments->count() > 0 ? '<span class="label label-default">'.$quote->attachments->count().'</span>' : '' !!}</a>
                            </li>
                            @endcan
                        </ul>
                        <div class="tab-content">
                            <div class="tab-pane active" id="tab-additional">
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{ trans('fi.terms_and_conditions') }}</label>
                                            {!! Form::textarea('terms', $quote->terms, ['id' => 'terms', 'class' => 'form-control', 'rows' => 5]) !!}
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{ trans('fi.footer') }}</label>
                                            {!! Form::textarea('footer', $quote->footer, ['id' => 'footer', 'class' => 'form-control', 'rows' => 5]) !!}
                                        </div>
                                    </div>
                                </div>

                                @if ($customFields)
                                    <div class="row">
                                        <div class="col-md-12">
                                            @include('custom_fields._custom_fields_unbound', ['object' => $quote])
                                        </div>
                                    </div>
                                @endif

                            </div>
                            @can('notes.view')
                            <div class="tab-pane" id="tab-notes">
                                <div class="row">
                                    <div class="col-lg-12">
                                        @include('notes._js_timeline', ['object' => $quote, 'model' => 'FI\Modules\Quotes\Models\Quote', 'hideHeader' => true, 'showPrivateCheckbox' => 1])
                                        <div id="note-timeline-container"></div>
                                    </div>
                                </div>
                            </div>
                            @endcan
                            @can('attachments.view')
                            <div class="tab-pane" id="tab-attachments">
                                <div class="row">
                                    <div class="col-lg-12">
                                        @include('attachments._table', ['object' => $quote, 'model' => 'FI\Modules\Quotes\Models\Quote'])
                                    </div>
                                </div>
                            </div>
                            @endcan
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <div class="col-lg-2">

            <div id="div-totals">
                @include('quotes._edit_totals')
            </div>

            <div class="box box-primary">

                <div class="box-header">
                    <h3 class="box-title">{{ trans('fi.options') }}</h3>
                </div>

                <div class="box-body">

                    <div class="form-group">
                        <label>{{ trans('fi.quote') }} #</label>
                        {!! Form::text('number', $quote->number, ['id' => 'number', 'class' =>
                        'form-control
                        input-sm']) !!}
                    </div>

                    <div class="form-group">
                        <label>{{ trans('fi.date') }}</label>
                        {!! Form::text('quote_date', $quote->formatted_quote_date, ['id' =>
                        'quote_date', 'class' => 'form-control input-sm']) !!}
                    </div>

                    <div class="form-group">
                        <label>{{ trans('fi.expires') }}</label>
                        {!! Form::text('expires_at', $quote->formatted_expires_at, ['id' => 'expires_at', 'class'
                        => 'form-control input-sm']) !!}
                    </div>

                    <div class="form-group">
                        <label>{{ trans('fi.discount') }}</label>

                        <div class="input-group">
                            {!! Form::text('discount', $quote->formatted_numeric_discount, ['id' =>
                            'discount', 'class' => 'form-control input-sm']) !!}
                            <span class="input-group-addon">%</span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>{{ trans('fi.currency') }}</label>
                        {!! Form::select('currency_code', $currencies, $quote->currency_code, ['id' =>
                        'currency_code', 'class' => 'form-control input-sm', 'style' => config('fi.baseCurrency') != $quote->currency_code ? 'background:#fff8dc' : '']) !!}
                    </div>

                    <div class="form-group">
                        <label>{{ trans('fi.exchange_rate') }}</label>

                        <div class="input-group">
                            {!! Form::text('exchange_rate', $quote->exchange_rate, ['id' =>
                            'exchange_rate', 'class' => 'form-control input-sm', 'style' => config('fi.baseCurrency') != $quote->currency_code ? 'background:#fff8dc' : '']) !!}
                            <span class="input-group-btn">
                                <button class="btn btn-default btn-sm" id="btn-update-exchange-rate" type="button"
                                        data-toggle="tooltip" data-placement="left"
                                        title="{{ trans('fi.update_exchange_rate') }}"><i class="fa fa-refresh"></i>
                                </button>
                            </span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>{{ trans('fi.status') }}</label>
                        {!! Form::select('status', $statuses, $quote->status,
                        ['id' => 'status', 'class' => 'form-control input-sm']) !!}
                    </div>

                    <div class="form-group">
                        <label>{{ trans('fi.template') }}</label>
                        {!! Form::select('template', $templates, $quote->template,
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
            <button type="button" class="btn btn-primary btn-save-quote"><i
                        class="fa fa-save"></i> {{ trans('fi.save') }}</button>
            <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown">
                <span class="caret"></span>
            </button>
            <ul class="dropdown-menu dropdown-menu-right" role="menu">
                <li><a href="#" class="btn-save-quote"
                       data-apply-exchange-rate="1">{{ trans('fi.save_and_apply_exchange_rate') }}</a></li>
            </ul>
        </div>
    </div>
    <div><br><br><br><br><br><br></div>

    <div class="clearfix"></div>
</section>
