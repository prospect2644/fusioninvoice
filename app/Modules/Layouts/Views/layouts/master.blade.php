<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
    <meta name="csrf-token" content="{{ csrf_token() }}"/>

    <title>{{ config('fi.headerTitleText') }}</title>

    @include('layouts._head')

    @include('layouts._js_global')

    @yield('head')

    @yield('javascript')

    @include('layouts._alertifyjs')

</head>
<body class="{{ $skinClass }} sidebar-mini fixed">

<div class="wrapper">

    @include('layouts._header')

    <aside class="main-sidebar">

        <section class="sidebar">

            @if (config('fi.displayProfileImage'))
                <div class="user-panel">
                    <div class="pull-left image">
                        <i class="fa initials">
                            {!! auth()->user()->getAvatar(40, false) !!}
                        </i>
                    </div>
                    <div class="pull-left info">
                        <p>{!! auth()->user()->name !!}</p>
                    </div>
                </div>
            @endif

            <ul class="sidebar-menu">
                <li class="{{ $urlSegment1 == 'dashboard' ? 'active' : '' }}">
                    <a href="{{ route('dashboard.index') }}">
                        <i class="fa fa-dashboard"></i> <span>{{ trans('fi.dashboard') }}</span>
                    </a>
                </li>
                @can('clients.view')
                <li class="{{ $urlSegment1 == 'clients' ? 'active' : '' }}">
                    <a href="{{ route('clients.index', ['status' => 'active']) }}">
                        <i class="fa fa-users"></i> <span>{{ trans('fi.clients') }}</span>
                    </a>
                </li>
                @endcan
                @can('quotes.view')
                <li class="{{ $urlSegment1 == 'quotes' ? 'active' : '' }}">
                    <a href="{{ route('quotes.index', ['status' => config('fi.quoteStatusFilter')]) }}">
                        <i class="fa fa-file-text-o"></i> <span>{{ trans('fi.quotes') }}</span>
                    </a>
                </li>
                @endcan
                @can('invoices.view')
                <li class="{{ $urlSegment1 == 'invoices' ? 'active' : '' }}">
                    <a href="{{ route('invoices.index', ['status' => config('fi.invoiceStatusFilter')]) }}">
                        <i class="fa fa-file-text"></i> <span>{{ trans('fi.invoices') }}</span>
                    </a>
                </li>
                @endcan
                @can('recurring_invoices.view')
                <li class="{{ $urlSegment1 == 'recurring_invoices' ? 'active' : '' }}">
                    <a href="{{ route('recurringInvoices.index') }}">
                        <i class="fa fa-refresh"></i> <span>{{ trans('fi.recurring_invoices') }}</span>
                    </a>
                </li>
                @endcan
                @can('payments.view')
                <li class="{{ $urlSegment1 == 'payments' ? 'active' : '' }}">
                    <a href="{{ route('payments.index') }}">
                        <i class="fa fa-credit-card"></i> <span>{{ trans('fi.payments') }}</span>
                    </a>
                </li>
                @endcan
                @can('expenses.view')
                <li class="{{ $urlSegment1 == 'expenses' ? 'active' : '' }}">
                    <a href="{{ route('expenses.index') }}">
                        <i class="fa fa-bank"></i> <span>{{ trans('fi.expenses') }}</span>
                    </a>
                </li>
                @endcan
                <li class="{{ $urlSegment1 == 'task' ? 'active' : '' }}">
                    <a href="{{ route('task.index') }}">
                        <i class="fa fa-tasks"></i> <span>{{ trans('fi.tasks') }}</span>
                    </a>
                </li>
                <li class="treeview {{ $urlSegment1 == 'report' ? 'active' : '' }}">
                    <a href="#">
                        <i class="fa fa-bar-chart-o"></i>
                        <span>{{ trans('fi.reports') }}</span>
                        <i class="fa fa-angle-left pull-right"></i>
                    </a>

                    <ul class="treeview-menu {{ $urlSegment1 == 'report' ? 'menu-open' : '' }}">
                        @can('client_statement.view')
                        <li class="{{ $urlSegment2 == 'client_statement' ? 'active' : '' }}"><a
                                    href="{{ route('reports.clientStatement') }}"><i
                                        class="fa fa-caret-right"></i> {{ trans('fi.client_statement') }}</a></li>
                        @endcan
                        @can('expense_list.view')
                        <li class="{{ $urlSegment2 == 'expense_list' ? 'active' : '' }}"><a
                                    href="{{ route('reports.expenseList') }}"><i
                                        class="fa fa-caret-right"></i> {{ trans('fi.expense_list') }}</a></li>
                        @endcan
                        @can('item_sales.view')
                        <li class="{{ $urlSegment2 == 'item_sales' ? 'active' : '' }}"><a
                                    href="{{ route('reports.itemSales') }}"><i
                                        class="fa fa-caret-right"></i> {{ trans('fi.item_sales') }}</a></li>
                        @endcan
                        @can('payments_collected.view')
                        <li class="{{ $urlSegment2 == 'payments_collected' ? 'active' : '' }}"><a
                                    href="{{ route('reports.paymentsCollected') }}"><i
                                        class="fa fa-caret-right"></i> {{ trans('fi.payments_collected') }}</a></li>
                        @endcan
                        @can('profit_and_loss.view')
                        <li class="{{ $urlSegment2 == 'profit_loss' ? 'active' : '' }}"><a
                                    href="{{ route('reports.profitLoss') }}"><i
                                        class="fa fa-caret-right"></i> {{ trans('fi.profit_and_loss') }}</a></li>
                        @endcan
                        @can('revenue_by_client.view')
                        <li class="{{ $urlSegment2 == 'revenue_by_client' ? 'active' : '' }}"><a
                                    href="{{ route('reports.revenueByClient') }}"><i
                                        class="fa fa-caret-right"></i> {{ trans('fi.revenue_by_client') }}</a></li>
                        @endcan
                        @can('tax_summary.view')
                        <li class="{{ $urlSegment2 == 'tax_summary' ? 'active' : '' }}"><a
                                    href="{{ route('reports.taxSummary') }}"><i
                                        class="fa fa-caret-right"></i> {{ trans('fi.tax_summary') }}</a></li>
                        @endcan
                        @can('recurring_invoice_list.view')
                        <li class="{{ $urlSegment2 == 'recurring_invoice_list' ? 'active' : '' }}"><a
                                    href="{{ route('reports.recurringInvoiceList') }}"><i
                                        class="fa fa-caret-right"></i> {{ trans('fi.recurring_invoice_list') }}</a></li>
                        @endcan

                        <li class="{{ $urlSegment2 == 'client_invoice' ? 'active' : '' }}"><a
                                    href="{{ route('reports.clientInvoice') }}"><i
                                        class="fa fa-caret-right"></i> {{ trans('fi.client_invoice') }}</a></li>

                        @foreach (config('fi.menus.reports') as $report)
                            @if (view()->exists($report))
                                @include($report)
                            @endif
                        @endforeach

                    </ul>
                </li>
                @foreach (config('fi.menus.navigation') as $menu)
                    @if (view()->exists($menu))
                        @include($menu)
                    @endif
                @endforeach
            </ul>

            @include('layouts._mru')

        </section>

    </aside>

    <div class="content-wrapper">
        @yield('content')
    </div>

</div>

<div id="modal-placeholder"></div>
<div id="attachment-modal-placeholder"></div>
<div id="note-modal-placeholder"></div>
<div class="modal modal-loader" style="display: none">
    <div class="ajax-loader">
        <img alt="" src="{{ asset('assets/dist/img/loader.gif') }}" width="100px"/>
        <h4>{{ trans('fi.loading') }}</h4>
    </div>
</div>

</body>
</html>