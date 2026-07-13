<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{{ trans('fi.welcome') }}</title>
    <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
    <meta name="csrf-token" content="{{ csrf_token() }}"/>
    
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">
    <link rel="mask-icon" href="{{ asset('safari-pinned-tab.svg') }}" color="#5bbad5">
    <meta name="msapplication-TileColor" content="#da532c">
    <meta name="theme-color" content="#ffffff">

    <link href="{{ asset('assets/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ asset('assets/font-awesome/css/font-awesome.min.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ asset('assets/dist/css/AdminLTE.min.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ asset('assets/style.css') }}" rel="stylesheet" type="text/css"/>

    @if (file_exists(base_path('custom/custom.css')))
        <link href="{{ asset('custom/custom.css') }}" rel="stylesheet" type="text/css"/>
    @endif

</head>
<body class="login-page" style="background: rgba(23, 171, 230, 0.45);">
<div class="login-box">
    <div class="login-logo">
        <b>Fusion</b>Invoice
    </div>
    <div class="login-box-body">
        @include('layouts._alertifyjs')
        @include('layouts._alerts')
        {!! Form::open(['route' => 'session.attempt', 'onsubmit' => "this.submit_button.disabled = true;"]) !!}
        <div class="form-group has-feedback">
            <input type="email" name="email" id="email" class="form-control" placeholder="{{ trans('fi.email') }}">
            <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
        </div>
        <div class="form-group has-feedback">
            <input type="password" name="password" class="form-control" placeholder="{{ trans('fi.password') }}">
            <span class="glyphicon glyphicon-lock form-control-feedback"></span>
        </div>
        @if(config('fi.useCaptchInLogin'))
            <div class="form-group has-feedback">
                <div class="row mb-10">
                    <div class="col-xs-5">
                        <div id="captcha-container">{!! captcha_img('math') !!}</div>
                    </div>
                    <div class="col-xs-2 refreshcaptcha">
                        <a href="javascript:void(0)" id="refresh-captcha" title="{{ trans('fi.refresh_captcha') }}"><i
                                    class="fa fa-fw fa-refresh"></i></a>
                    </div>
                </div>

                <input type="text" name="captcha" class="form-control" placeholder="{{ trans('fi.type_captcha') }}">

            </div>
        @endif
        <div class="row">
            <div class="col-xs-8">
                <div class="checkbox">
                    <label>
                        <input type="hidden" name="remember_me" value="0">
                        <input type="checkbox" name="remember_me" value="1"> {{ trans('fi.remember_me') }}
                    </label>
                </div>
            </div>
            <div class="col-xs-4">
                <button type="submit" name="submit_button"
                        class="btn btn-primary btn-block btn-flat">{{ trans('fi.sign_in') }}</button>
            </div>
        </div>
        {!! Form::close() !!}

    </div>
</div>

<script src="{{ asset('assets/plugins/jQuery/jQuery.min.js') }}"></script>
<script src="{{ asset('assets/bootstrap/js/bootstrap.min.js') }}" type="text/javascript"></script>

<script type="text/javascript">
    $(function () {
        $('#email').focus();
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $('#refresh-captcha').click(function () {
            $.post("{{ route('session.refresh_captcha') }}", function (response) {
                $('#captcha-container').html(response);
            });
        });
    });
</script>

</body>
</html>