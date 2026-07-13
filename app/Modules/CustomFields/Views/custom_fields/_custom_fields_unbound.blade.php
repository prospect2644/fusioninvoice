@include('layouts._datetimepicker')

<script type="text/javascript">
    $(function () {
        $('.custom-select2').select2();
    });
</script>

{{--For fetch and display single custom field--}}
@if(isset($label))
    <div class="row">
        @foreach ($customFields as $customField)
            @if($customField->field_label == $label)
                <div class="col-md-{{ config('fi.customFieldsDisplayColumn') }}">
                    @include('custom_fields._custom_fields')
                </div>
            @endif
        @endforeach
    </div>
@else
    <div class="row">
        @foreach ($customFields as $customField)
            <div class="col-md-{{ config('fi.customFieldsDisplayColumn') }}">
                @include('custom_fields._custom_fields')
            </div>
        @endforeach
    </div>
@endif
