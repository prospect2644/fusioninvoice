@extends('layouts.master')

@section('content')

    <script type="text/javascript">
        $(function () {
            $('#name').focus();

            $('#field_type').on('change', function () {
                var field_type = $(this).val();
                switch (field_type) {
                    case 'dropdown':
                    case 'radio':
                        var dropdown_default_meta = {
                            "default": "option1",
                            "options": {"option1": "My Value 1", "option2": "My Value 2"}
                        };
                        $('#field_meta').val(JSON.stringify(dropdown_default_meta, undefined, 4)).attr('disabled', false);
                        break;
                    case 'textarea':
                        var textarea_default_meta = {
                            "rows": "4"
                        };
                        $('#field_meta').val(JSON.stringify(textarea_default_meta, undefined, 4)).attr('disabled', false);

                        break;
                    case 'currency':
                        var currency_default_meta = {
                            "symbol": "$"
                        };
                        $('#field_meta').val(JSON.stringify(currency_default_meta, undefined, 4)).attr('disabled', false);

                        break;
                    case 'tagselection':
                        var tagselection_default_meta = {
                            "default": "tag1",
                            "options": {"tag1": "Tag 1", "tag2": "Tag 2"}
                        };
                        $('#field_meta').val(JSON.stringify(tagselection_default_meta, undefined, 4)).attr('disabled', false);

                        break;
                    default:
                        $('#field_meta').val('').attr('disabled', true);
                }
            });

            $('.save_custom_field').click(function () {

                var field_type = $('#field_type').val();
                switch (field_type) {
                    case 'dropdown':
                    case 'radio':
                    case 'textarea':
                    case 'currency':
                    case 'tagselection':
                        var field_meta = $('#field_meta').val();
                        break;
                    default:
                        var field_meta = '';
                }

                // Lets validate JSON string
                try {
                    field_meta = field_meta != '' ? JSON.parse(field_meta) : field_meta;
                } catch (e) {
                    alertify.error('{!! trans('fi.invalid_json') !!}', 10);
                    return false;
                }

                //let's check textarea rows value.
                if($('#field_type').val() == 'textarea'){

                    if(field_meta.rows !== undefined && field_meta.rows > 25){
                        alertify.error('{!! trans('fi.textarea_rows_limit', ['limit' => 25]) !!}', 10);
                        return false;
                    }

                }

                let tableName = '{{ $selectedTable }}';
                if(0 < $('select#tbl_name').length) {
                    tableName = $('select#tbl_name').val();
                }
                let actionUrl = $('form').attr('action') + '?table=' + tableName;

                $(this).attr('disabled', true);

                $('form').attr('action', actionUrl).submit();

            });
        });
    </script>

    @if ($editMode == true)
        {!! Form::model($customField, ['route' => ['customFields.update', $customField->id]]) !!}
    @else
        {!! Form::open(['route' => 'customFields.store']) !!}
    @endif

    <section class="content-header">
        <h1 class="pull-left">
            {{ trans('fi.custom_field_form') }}
        </h1>

        <div class="pull-right">
            <a href="{{ route('customFields.index') }}" class="btn btn-default">Cancel</a>
            <button class="btn btn-primary save_custom_field"><i class="fa fa-save"></i> {{ trans('fi.save') }}</button>
        </div>
        <div class="clearfix"></div>
    </section>

    <section class="content">

        @include('layouts._alerts')

        <div class="row">

            <div class="col-md-12">

                <div class="box box-primary">

                    <div class="box-body">

                        <div class="form-group">
                            <label>{{ trans('fi.table_name') }}: </label>
                            @if ($editMode == true)
                                {!! Form::text('tbl_name', $tableNames[$customField->tbl_name], ['id' => 'tbl_name', 'readonly' => 'readonly', 'class' => 'form-control']) !!}
                            @else
                                {!! Form::select('tbl_name', $tableNames, $selectedTable, ['id' => 'tbl_name', 'class' => 'form-control']) !!}
                            @endif
                        </div>

                        <div class="form-group">
                            <label>{{ trans('fi.field_label') }}: </label>
                            {!! Form::text('field_label', null, ['id' => 'field_label', 'class' => 'form-control']) !!}
                        </div>

                        <div class="form-group">
                            <label>{{ trans('fi.field_type') }}: </label>
                            {!! Form::select('field_type', $fieldTypes, null, ['id' => 'field_type', 'class' => 'form-control']) !!}
                        </div>

                        <div class="form-group">
                            <label>{{ trans('fi.field_meta') }}: </label>
                            @if ($editMode == true)
                                {!! Form::textarea('field_meta', null, ['id' => 'field_meta', 'class' => 'form-control', isset($customField->field_type) && in_array($customField->field_type,$fieldWithoutMeta) ? 'disabled' : '']) !!}
                            @else
                                {!! Form::textarea('field_meta', null, ['id' => 'field_meta', 'class' => 'form-control', 'disabled']) !!}
                            @endif
                        </div>

                    </div>

                </div>

            </div>

        </div>

    </section>

    {!! Form::close() !!}
@stop