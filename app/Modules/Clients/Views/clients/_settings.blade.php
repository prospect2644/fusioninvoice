<div class="row">
    <div class="col-md-2">
        <div class="form-group">
            <label>{{ trans('fi.active') }}:</label>
            {!! Form::select('active', ['0' => trans('fi.no'), '1' => trans('fi.yes')], ((isset($editMode) and $editMode) ? null : 1), ['id' => 'active', 'class' => 'form-control']) !!}
        </div>
    </div>
    <div class="col-md-2">
        <div class="form-group">
            <label>{{ trans('fi.invoice_prefix') }}:</label>
            {!! Form::text('invoice_prefix', null, ['id' => 'invoice_prefix', 'class' => 'form-control']) !!}
        </div>
    </div>
    <div class="col-md-2">
        <div class="form-group">
            <label>{{ trans('fi.default_currency') }}: </label>
            {!! Form::select('currency_code', $currencies, $client->currency_code ?? config('fi.baseCurrency'), ['id' => 'currency_code', 'class' => 'form-control']) !!}
        </div>
    </div>
    <div class="col-md-2">
        <div class="form-group">
            <label>{{ trans('fi.language') }}: </label>
            {!! Form::select('language', $languages, $client->language ?? config('fi.language'), ['id' => 'language', 'class' => 'form-control']) !!}
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>{{ trans('fi.parent_account') }}: </label>
            {!! Form::select('parent_client_id', $parentClients, null, ['id' => 'parent_client_id', 'class' => 'form-control']) !!}
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-3">
        <div class="form-group">
            <label>{{ trans('fi.timezone') }}: </label>
            {!! Form::select('timezone', $timezones, null, ['id' => 'timezone', 'class' => 'form-control']) !!}
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>{{ trans('fi.automatic_email_payment_receipts') }}: </label>
            {!! Form::select('automatic_email_payment_receipt', ['default' => trans('fi.default'), 'yes' => trans('fi.yes'), 'no' => trans('fi.no')], null, ['class' => 'form-control']) !!}
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>{{ trans('fi.automatic_email_on_recur') }}: </label>
            {!! Form::select('automatic_email_on_recur', ['default' => trans('fi.default'), 'yes' => trans('fi.yes'), 'no' => trans('fi.no')], null, ['id' => 'automatic_email_on_recurring_invoice', 'class' => 'form-control']) !!}
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="form-group">
            {!! Form::checkbox('allow_client_center_login', 1, isset($client->user) ? 1 : '', ['id' => 'allow_client_center_login', 'class' => '']) !!}
            <label for="allow_client_center_login">{{ trans('fi.allow_client_center_login') }}: </label>
        </div>
    </div>
</div>

<div class="row client-login-detail" style="display: {{ !isset($client->user) ? 'none' : '' }}">
    <div class="col-md-6">
        <div class="form-group">
            <label>{{ trans('fi.client_name') }}:</label>
            {!! Form::text('cname', isset($client->unique_name) ? $client->unique_name : null , ['id' => 'cname', 'class' => 'form-control', 'disabled' => true]) !!}
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label>{{ trans('fi.email_address') }}: </label>
            {!! Form::text('cemail', isset($client->client_email) ? $client->client_email : null , ['id' => 'cemail', 'class' => 'form-control', 'disabled' => true]) !!}
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label>{{ trans('fi.password') }}: </label>
            {!! Form::password('password', ['id' => 'password', 'class' => 'form-control']) !!}
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label>{{ trans('fi.password_confirmation') }}: </label>
            {!! Form::password('password_confirmation', ['id' => 'password_confirmation',
            'class' => 'form-control']) !!}
        </div>
    </div>
</div>

<script type="text/javascript">
    $(function () {
        $('#allow_client_center_login').click(function () {
            if ($(this).prop("checked") == true) {
                $('.client-login-detail').show();
                $('#password,#password_confirmation').attr('disabled', false);
            } else {
                $('.client-login-detail').hide();
                $('#password,#password_confirmation').val('').attr('disabled', true);
            }
        });

        $('#client_email').change(function () {
            $('#cemail').val($(this).val());
        });

        $('#name').change(function () {
            $('#cname').val($(this).val());
        });
    });

    $(document).ready(function () {
        $('#cname').val($('#name').val());
        $('#cemail').val($('#client_email').val());

        if ($('#allow_client_center_login').prop("checked") == true) {
            $('.client-login-detail').show();
            $('#password,#password_confirmation').attr('disabled', false).val('');
        } else {
            $('#password,#password_confirmation').attr('disabled', true).val('');
        }
    });
</script>