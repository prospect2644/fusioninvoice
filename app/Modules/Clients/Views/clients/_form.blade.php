@include('clients._js_unique_name')
@include('layouts._select2')
<div class="row">
    <div class="col-md-4 client-active-resize">
        <div class="form-group">
            <label>* {{ trans('fi.client_name') }}:</label>
            {!! Form::text('name', null, ['id' => 'name', 'class' => 'form-control']) !!}
            <p class="help-block">
                <small>{{ trans('fi.help_text_client_name') }}
                    <a href="javascript:void(0)" id="btn-show-unique-name"
                       tabindex="-1">{{ trans('fi.view_unique_name') }}</a>
                </small>
            </p>
        </div>
    </div>
    <div class="col-md-3" id="col-client-unique-name" style="display: none;">
        <div class="form-group">
            <label>* {{ trans('fi.unique_name') }}:</label>
            {!! Form::text('unique_name', null, ['id' => 'unique_name', 'class' => 'form-control']) !!}
            <p class="help-block">
                <small>{{ trans('fi.help_text_client_unique_name') }}</small>
            </p>
        </div>
    </div>
    <div class="col-md-4 client-active-resize">
        <div class="form-group">
            <label>{{ trans('fi.email_address') }}: </label>
            {!! Form::text('client_email', null, ['id' => 'client_email', 'class' => 'form-control']) !!}
        </div>
    </div>
    <div class="col-md-4 client-active-resize">
        <div class="col-md-6">
            <div class="form-group">
                <label>{{ trans('fi.type') }}:</label>
                {!! Form::select('type', $types, null, ['id' => 'type', 'class' => 'form-control']) !!}
            </div>
        </div>
    </div>

</div>

<div class="form-group">
    <label>{{ trans('fi.address') }}: </label>
    {!! Form::textarea('address', null, ['id' => 'address', 'class' => 'form-control', 'rows' => 4]) !!}
</div>

<div class="row">
    <div class="col-md-3">
        <div class="form-group">
            <label>{{ trans('fi.city') }}: </label>
            {!! Form::text('city', null, ['id' => 'city', 'class' => 'form-control']) !!}
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>{{ trans('fi.state') }}: </label>
            {!! Form::text('state', null, ['id' => 'state', 'class' => 'form-control']) !!}
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>{{ trans('fi.postal_code') }}: </label>
            {!! Form::text('zip', null, ['id' => 'zip', 'class' => 'form-control']) !!}
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>{{ trans('fi.country') }}: </label>
            {!! Form::select('country', $countries, null, ['id' => 'country', 'class' => 'form-control', 'placeholder' => '']) !!}
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-3">
        <div class="form-group">
            <label>{{ trans('fi.phone_number') }}: </label>
            {!! Form::text('phone', null, ['id' => 'phone', 'class' => 'form-control']) !!}
        </div>
    </div>

    <div class="col-md-3">
        <div class="form-group">
            <label>{{ trans('fi.fax_number') }}: </label>
            {!! Form::text('fax', null, ['id' => 'fax', 'class' => 'form-control']) !!}
        </div>
    </div>

    <div class="col-md-3">
        <div class="form-group">
            <label>{{ trans('fi.mobile_number') }}: </label>
            {!! Form::text('mobile', null, ['id' => 'mobile', 'class' => 'form-control']) !!}
        </div>
    </div>

    <div class="col-md-3">
        <div class="form-group">
            <label>{{ trans('fi.web_address') }}: </label>
            {!! Form::text('web', null, ['id' => 'web', 'class' => 'form-control']) !!}
        </div>
    </div>

    <div class="col-md-10">
        <div class="form-group">
            <span style="color: firebrick;background-color: pink;">
            <label>Important Note: </label>
            </span>
            {!! Form::textarea('important_note', null, ['id' => 'important_note', 'class' => 'form-control', 'rows' => 2]) !!}
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-3">
        <div class="form-group">
            <label>{{ trans('fi.tags') }}: </label>
            {!! Form::select('tags[]', $tags, $selectedTags, ['class' => 'form-control client-tags','multiple' => true, 'id' => 'client-tags', 'style' => 'width:100%']) !!}
        </div>
    </div>
</div>

@if ($customFields)
    @include('custom_fields._custom_fields_unbound', ['object' => isset($client) ? $client : []])
@endif

<script type="text/javascript">
    $(function () {
        $('#name').focus();

        $('#client-tags').select2({tags: true, tokenSeparators: [",", " "]});

        $('#btn-delete-custom-img').click(function () {
            var url = "{{ route('clients.deleteImage', [isset($client->id) ? $client->id : '','field_name' => '']) }}";
            $.post(url + '/' + $(this).data('field-name')).done(function () {
                $('.custom_img').html('');
            });
        });

        $('#country').select2({
            placeholder: "{{ trans('fi.select_country') }}"
        });

    });
</script>
