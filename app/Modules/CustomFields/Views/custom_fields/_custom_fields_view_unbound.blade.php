{{--For fetch and display single custom field--}}
@if(isset($label))
    <table class="table table-striped">
        @foreach ($customFields as $customField)
            @if($customField->field_label == $label)
                <tr>
                    <td class="col-md-2"><label>{!! $customField->field_label !!}</label></td>
                    <td class="col-md-10">
                        @switch($customField->field_type)
                        @case('text')
                        @case('textarea')
                        @case('date')
                        @case('datetime')
                        @case('currency')
                        @case('phone')
                        @case('decimal')
                        @case('integer')
                        {!! nl2br($client->customField($customField->field_label)) !!}
                        @break
                        @case('email')
                        @if($client->custom->{$customField->column_name} != '')
                            <a href="mailto:{!! $client->custom->{$customField->column_name} !!}">{!! $client->custom->{$customField->column_name} !!}</a>
                        @endif
                        @break
                        @case('url')
                        @if($client->custom->{$customField->column_name} != '')
                            <a href="{!! $client->custom->{$customField->column_name} !!}" target="_blank">{!! $client->custom->{$customField->column_name} !!}</a>
                        @endif
                        @break
                        @case('checkbox')
                        {!! Form::checkbox('custom[' . $customField->column_name . ']',1, isset($object->custom->{$customField->column_name}) && $object->custom->{$customField->column_name} == 1 ? true : false, ['class' => 'custom-form-field', 'disabled']) !!}
                        @break
                        @case('radio')
                        @case('dropdown')
                        <?php $options = (array)$customField->options; ?>
                        {!! $client->custom->{$customField->column_name} != '' && isset($options[$client->custom->{$customField->column_name}]) ? $options[$client->custom->{$customField->column_name}] : '' !!}
                        @break
                        @case('tagselection')
                        <?php $tags = json_decode($object->custom->{$customField->column_name}); ?>
                        @if(!empty($tags) && count($tags) > 0)
                            @foreach ($tags as $tag)
                                <span class="label label-default" style="font-size: 85%;">{{ $customField->options->{$tag} }}</span>
                            @endforeach
                        @endif
                        @break
                        @case('image')
                        @if(isset($object->custom->{$customField->column_name}))
                            <div class="custom_img">{!! $object->custom->imageView($customField->column_name,100) !!}</div>
                        @endif
                        @default
                        @endswitch
                    </td>
                </tr>
            @endif
        @endforeach
    </table>
@else
    <table class="table table-striped">
        <?php
        $i = 0;
        if (config('fi.customFieldsDisplayColumn') == 12)
        {
            $columns = 1;
        }
        elseif (config('fi.customFieldsDisplayColumn') == 6)
        {
            $columns = 2;
        }
        elseif (config('fi.customFieldsDisplayColumn') == 4)
        {
            $columns = 3;
        }
        ?>
        @foreach ($customFields as $customField)
            <?php $i++; ?>
            @if ($i % $columns == 1)
                <tr>
                    @endif
                    <td class="col-md-2"><label>{!! $customField->field_label !!}</label></td>
                    <td class="col-md-{{ config('fi.customFieldsDisplayColumn') - 2 }}">
                        @switch($customField->field_type)
                        @case('text')
                        @case('textarea')
                        @case('date')
                        @case('datetime')
                        @case('currency')
                        @case('phone')
                        @case('decimal')
                        @case('integer')
                        {!! nl2br($client->customField($customField->field_label)) !!}
                        @break
                        @case('email')
                        @if($client->custom->{$customField->column_name} != '')
                            <a href="mailto:{!! $client->custom->{$customField->column_name} !!}">{!! $client->custom->{$customField->column_name} !!}</a>
                        @endif
                        @break
                        @case('url')
                        @if($client->custom->{$customField->column_name} != '')
                            <a href="{!! $client->custom->{$customField->column_name} !!}" target="_blank">{!! $client->custom->{$customField->column_name} !!}</a>
                        @endif
                        @break
                        @case('checkbox')
                        {!! Form::checkbox('custom[' . $customField->column_name . ']',1, isset($object->custom->{$customField->column_name}) && $object->custom->{$customField->column_name} == 1 ? true : false, ['class' => 'custom-form-field', 'disabled']) !!}
                        @break
                        @case('radio')
                        @case('dropdown')
                        <?php $options = (array)$customField->options; ?>
                        {!! $client->custom->{$customField->column_name} != '' && isset($options[$client->custom->{$customField->column_name}]) ? $options[$client->custom->{$customField->column_name}] : '' !!}
                        @break
                        @case('tagselection')
                        <?php $tags = json_decode($object->custom->{$customField->column_name}); ?>
                        @if(!empty($tags) && count($tags) > 0)
                            @foreach ($tags as $tag)
                                <span class="label label-default" style="font-size: 85%;">{{ $customField->options->{$tag} }}</span>
                            @endforeach
                        @endif
                        @break
                        @case('image')
                        @if(isset($object->custom->{$customField->column_name}))
                            <div class="custom_img">{!! $object->custom->imageView($customField->column_name,100) !!}</div>
                        @endif
                        @default
                        @endswitch
                    </td>
                    @if ($i % $columns == 0)
                </tr>
            @endif
        @endforeach
    </table>
@endif
