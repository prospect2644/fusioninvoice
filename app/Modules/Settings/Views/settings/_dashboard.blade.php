<h4 style="font-weight: bold; clear: both; margin-top: 20px;">
    <i class="fa fa-dashboard"></i> {{ trans('fi.dashboard_widgets_date_options') }}
</h4>
<hr style="margin-top: 0; margin-bottom: 10px; border-top: 1px solid #d3d3d3;">
<div class="row">
    <div class="col-md-2" style="margin-left: 10px;">
        <div class="form-group">
            <label></label>
            {!! Form::select('setting[dashboardWidgetsDateOption]', $dashboardWidgetsDateOptions,isset($settings['dashboardWidgetsDateOption']) ? $settings['dashboardWidgetsDateOption'] : 'this_month', ['id'=> 'dashboard-widgets-date-options','class' => 'form-control']) !!}
        </div>
    </div>
    <div id="dashboard-widget-dates"
         style="display: {{ isset($settings['dashboardWidgetsDateOption']) && $settings['dashboardWidgetsDateOption'] == 'custom_date_range' || old('setting.dashboardWidgetsDateOption') == 'custom_date_range' ? 'block' : 'none' }};">
        <div class="col-md-2" style="margin-left: 10px;">
            <div class="form-group">
                <label>{{ trans('fi.from_date') }} (yyyy-mm-dd):</label>
                {!! Form::text('setting[dashboardWidgetsFromDate]', isset($settings['dashboardWidgetsFromDate']) ? $settings['dashboardWidgetsFromDate'] : '', ['class' => 'form-control', 'id' => 'dashboard-widgets-from-date']) !!}
            </div>
        </div>
        <div class="col-md-2" style="margin-left: 10px;">
            <div class="form-group">
                <label>{{ trans('fi.to_date') }} (yyyy-mm-dd):</label>
                {!! Form::text('setting[dashboardWidgetsToDate]', isset($settings['dashboardWidgetsToDate']) ? $settings['dashboardWidgetsToDate'] : '', ['class' => 'form-control', 'id' => 'dashboard-widgets-to-date']) !!}
            </div>
        </div>
    </div>
</div>

@foreach ($dashboardWidgets as $widget)

@section('trasnlateWidgetNamesSection')
    @switch(strtolower($widget))
    @case('invoicesummary')
    {{ $widgetIcon = '<i class="fa fa-file-text"></i>' }}
    {{ $widgetHdr = trans('fi.invoice_summary') }}
    @break
    @case('quotesummary')
    {{ $widgetIcon = '<i class="fa fa-file-text-o"></i>' }}
    {{ $widgetHdr = trans('fi.quote_summary') }}
    @break
    @case('clientactivity')
    {{ $widgetIcon = '<i class="fa fa-child"></i>' }}
    {{ $widgetHdr = trans('fi.recent_client_activity') }}
    @break
    @case('tasks')
    {{ $widgetIcon = '<i class="fa fa-list"></i>' }}
    {{ $widgetHdr = trans('fi.task_list') }}
    @break
    @case('clienttimeline')
    {{ $widgetIcon = '<i class="fa fa-list"></i>' }}
    {{ $widgetHdr = trans('fi.client_timeline') }}
    @break
    @default
    {{ $widgetIcon = '' }}
    {{ $widgetHdr = $widget }}
    @endswitch
@endsection

<h4 style="font-weight: bold; clear: both; margin-top: 20px;"> {!! $widgetIcon !!} {{ $widgetHdr }}</h4>
<hr style="margin-top: 0; margin-bottom: 10px; border-top: 1px solid #d3d3d3;">
<div class="row">

    <div class="col-md-2" style="margin-left: 10px;">
        <div class="form-group">
            <label>{{ trans('fi.enabled') }}: </label>
            {!! Form::select('setting[widgetEnabled' . $widget . ']', $yesNoArray,
            isset($settings['widgetEnabled' . $widget]) ? $settings['widgetEnabled' . $widget] : 0, ['id' => 'widgetEnabled' . $widget, 'class' => 'form-control']) !!}
        </div>
    </div>

    <div class="col-md-2">
        <div class="form-group">
            <label>{{ trans('fi.display_order') }}: </label>
            {!! Form::select('setting[widgetDisplayOrder' . $widget . ']', $displayOrderArray,
            isset($settings['widgetDisplayOrder' . $widget]) ? $settings['widgetDisplayOrder' . $widget] : 1,
            ['id' => 'widgetDisplayOrder' . $widget, 'class' => 'form-control']) !!}
        </div>
    </div>
    @if (strtolower($widget) == 'tasks' || strtolower($widget) == 'clienttimeline')
        <div class="col-md-2">
            <div class="form-group">
                <label>{{ trans('fi.column_width') }}: </label>
                {!! Form::select('setting[widgetColumnWidth' . $widget . ']', ['6'=>6,'8'=>8,'12'=>12],
                isset($settings['widgetColumnWidth' . $widget]) ? $settings['widgetColumnWidth' . $widget] :  6, ['id' => 'widgetColumnWidth' . $widget, 'class' =>
                'form-control']) !!}
            </div>
        </div>
    @else
        <div class="col-md-2">
            <div class="form-group">
                <label>{{ trans('fi.column_width') }}: </label>
                {!! Form::select('setting[widgetColumnWidth' . $widget . ']', $colWidthArray,
                isset($settings['widgetColumnWidth' . $widget]) ? $settings['widgetColumnWidth' . $widget] :  6, ['id' => 'widgetColumnWidth' . $widget, 'class' =>
                'form-control']) !!}
            </div>
        </div>
    @endif

    @if (strtolower($widget) == 'tasks')
        <div class="col-md-2">
            <div class="form-group">
                <label>{{ trans('fi.display_profile_image') }}: </label>
                {!! Form::select('setting[displayProfileImage]', $yesNoArray, config('fi.displayProfileImage'), ['class' => 'form-control']) !!}
            </div>
        </div>
        <div class="col-md-2">
            <div class="form-group">
                <label>{{ trans('fi.include_time_in_due_date') }}: </label>
                {!! Form::select('setting[includeTimeInTaskDueDate]', $yesNoArray, isset($settings['includeTimeInTaskDueDate']) ? $settings['includeTimeInTaskDueDate'] : 0, ['class' => 'form-control']) !!}
            </div>
        </div>
    @endif

</div>

@if (view()->exists($widget . 'WidgetSettings'))
    @include($widget . 'WidgetSettings')
@endif

@endforeach