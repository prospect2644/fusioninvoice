<!doctype html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    @php $invoice_type_title = ($invoice->type == 'credit_memo') ? trans('fi.credit_memo') : trans('fi.invoice'); @endphp
    <title>{{ $invoice_type_title }} #{{ $invoice->number }}</title>

    <style>
        @page {
            margin: 25px;
        }

        body {
            color: #001028;
            background: #FFFFFF;
            font-family: DejaVu Sans, Helvetica, sans-serif;
            font-size: 12px;
            margin-left: 0px;
            margin-right: 0px;
        }

        a {
            color: #5D6975;
            border-bottom: 1px solid currentColor;
            text-decoration: none;
        }

        h1 {
            color: #5D6975;
            font-size: 2.8em;
            line-height: 1.4em;
            font-weight: bold;
            margin: 0;
        }

        table {
            width: 100%;
            border-spacing: 0;
            margin-bottom: 20px;
            padding: 0 2px;
        }

        th, .section-header {
            padding: 5px 10px;
            color: #5D6975;
            border-bottom: 1px solid #C1CED9;
            white-space: nowrap;
            font-weight: normal;
            text-align: center;
        }

        @media only screen and (max-width: 600px) {
            th, .section-header {
                padding: 5px 4px;
            }
        }

        @media only screen and (max-width: 320px) {
            table {
                width: 100%;
                border-spacing: 0;
                margin-bottom: 20px;
                font-size: 10px;
                margin-left: 0px;
            }
        }

        td {
            padding: 10px 5px;
        }

        table.alternate tr:nth-child(odd) td {
            background: #F5F5F5;
        }

        th.amount, td.amount {
            text-align: right;
        }

        .info {
            color: #5D6975;
            font-weight: bold;
        }

        .terms {
            padding: 10px;
        }

        .footer {
            text-align: center;
            padding: 10px;
        }

        #cp-logo {
            max-width: 114px;
        }

    </style>
</head>
<body>

<table>
    <tr>
        <td style="width: {{ config('fi.qrCodeOnInvoiceQuote') == 1 ? 33 : 50 }}%;" valign="top">
            <h1>{{ mb_strtoupper($invoice_type_title) }}</h1>
            <span class="info">{{ mb_strtoupper($invoice_type_title) }} #</span>{{ $invoice->number }}<br>
            <span class="info">{{ mb_strtoupper(trans('fi.issued')) }}</span> {{ $invoice->formatted_created_at }}<br>
            <span class="info">{{ mb_strtoupper(trans('fi.due_date')) }}</span> {{ $invoice->formatted_due_at }}<br><br>
            <span class="info">{{ mb_strtoupper(trans('fi.bill_to')) }}</span><br>
            {{ $invoice->client->title != '' ? $invoice->client->title.' '.$invoice->client->name : $invoice->client->name }}<br>
            @if ($invoice->client->address) {!! $invoice->client->formatted_address !!}<br>@endif
        </td>
        @if(config('fi.qrCodeOnInvoiceQuote') == 1)
            <td style="width: 33%;" valign="top" align="center">
                <img alt="QR-Code" width=""
                     src="data:image/png;base64,{!! DNS2D::getBarcodePNG(route('clientCenter.public.invoice.show', [$invoice->url_key]),"QRCODE") !!}"
                     class="img-responsive">
            </td>
        @endif
        <td style="width: {{ config('fi.qrCodeOnInvoiceQuote') == 1 ? 33 : 50 }}%; text-align: right;" valign="top">
            {!! $invoice->companyProfile->logo() !!}<br>
            {{ $invoice->companyProfile->company }}<br>
            {!! $invoice->companyProfile->formatted_address !!}<br>
            @if ($invoice->companyProfile->phone) {{ $invoice->companyProfile->phone }}<br>@endif
            @if (isset($invoice->user->email)) <a href="mailto:{{ $invoice->user->email }}">{{ $invoice->user->email }}</a>@endif
        </td>
    </tr>
</table>

<table class="alternate">
    <thead>
    <tr>
        <th style="text-align: left;"><strong>{{ mb_strtoupper(trans('fi.product')) }}</strong></th>
        <th style="text-align: left;"><strong>{{ mb_strtoupper(trans('fi.description')) }}</strong></th>
        <th class="amount"><strong>{{ mb_strtoupper(trans('fi.quantity')) }}</strong></th>
        <th class="amount"><strong>{{ mb_strtoupper(trans('fi.price')) }}</strong></th>
        <th class="amount"><strong>{{ mb_strtoupper(trans('fi.total')) }}</strong></th>
    </tr>
    </thead>
    <tbody>
    @foreach ($invoice->items as $item)
        <tr>
            <td>{!! $item->name !!}</td>
            <td>{!! $item->formatted_description !!}</td>
            <td nowrap class="amount">{{ $item->formatted_quantity }}</td>
            <td nowrap class="amount">{{ $item->formatted_price }}</td>
            <td nowrap class="amount">{{ $item->amount->formatted_subtotal }}</td>
        </tr>
    @endforeach

    <tr>
        <td colspan="4" class="amount"><strong>{{ mb_strtoupper(trans('fi.subtotal')) }}</strong></td>
        <td class="amount">{{ $invoice->amount->formatted_subtotal }}</td>
    </tr>

    @if ($invoice->discount > 0)
        <tr>
            <td colspan="4" class="amount">{{ mb_strtoupper(trans('fi.discount')) }}</td>
            <td class="amount">{{ $invoice->amount->formatted_discount }}</td>
        </tr>
    @endif

    @foreach ($invoice->summarized_taxes as $tax)
        <tr>
            <td colspan="4" class="amount">{{ mb_strtoupper($tax->name) }} ({{ $tax->percent }})</td>
            <td class="amount">{{ $tax->total }}</td>
        </tr>
    @endforeach

    <tr>
        <td colspan="4" class="amount"><strong>{{ mb_strtoupper(trans('fi.total')) }}</strong></td>
        <td class="amount">{{ $invoice->amount->formatted_total }}</td>
    </tr>
    <tr>
        <td colspan="4" class="amount"><strong>{{ ($invoice->type=='credit_memo') ? mb_strtoupper(trans('fi.applied')) : mb_strtoupper(trans('fi.paid')) }}</strong></td>
        <td class="amount">{{ $invoice->amount->formatted_paid }}</td>
    </tr>
    <tr>
        <td colspan="4" class="amount"><strong>{{ mb_strtoupper(trans('fi.balance')) }}</strong></td>
        <td class="amount">{{ $invoice->amount->formatted_balance }}</td>
    </tr>
    </tbody>
</table>

<table>
    @if ($invoice->terms)
        <tr>
            <td colspan="2">
                <div class="section-header">{{ mb_strtoupper(trans('fi.terms_and_conditions')) }}</div>
            </td>
            <td colspan="2">
                <div class="terms">{!! $invoice->formatted_terms !!}</div>
            </td>
        </tr>
    @endif
    <tr>
        <td colspan="2">
            <div class="footer">{!! $invoice->formatted_footer !!}</div>
        </td>
    </tr>
    </tbody>
</table>

</body>
</html>
