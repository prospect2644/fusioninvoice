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

</head>
<body class="skin-fusioninvoice layout-boxed sidebar-mini">

<div class="wrapper" style="margin: 7% auto; margin-bottom:0px;">
    <div class="login-logo" style="background-color: #17abe6;">
        <b>Fusion</b>Invoice
    </div>

    <div class="content-wrapper-public">
        @yield('content')
    </div>

</div>

<div id="modal-placeholder"></div>

</body>
</html>