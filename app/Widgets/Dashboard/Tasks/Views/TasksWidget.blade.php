@include('layouts._datepicker')
@include('layouts._datetimepicker')
@include('layouts._select2')
@include('tasks.widget._js_widget')
@include('layouts._formdata')

<div id="tasks-dashboard-widget">

    <div class="box box-solid">
        <div class="box-header with-border">
            <h3 class="fa fa-list fa-2x"></h3>

            <h3 class="box-title">{{ trans('fi.task_list') }}</h3>

            <div class="box-tools pull-right">
                <button id="create-new-task" class="btn btn-box-tool create-task"
                        style="font-size: 1.05em; color: black;">
                    <i class="fa fa-plus"></i> {{ trans('fi.create_task') }}</button>
            </div>
        </div>

        <div class="box-body">
            <div class="box-tools pull-left">
                <button id="reload-task" class="btn btn-box-tool btn-box-tool-background refresh-task"
                        style="font-size: 1.04em; color: black;">
                    <i class="fa fa-refresh reload-task"></i> {{ trans('fi.refresh') }}</button>
            </div>

            <div class="btn-group pull-right form-inline task-list-filter">
                <div class="pull-left">
                    {!! Form::open(['method' => 'GET', 'url' => route('task.widget.list'), 'id' => 'tasks-filter-form', 'class' => 'form-inline inline']) !!}
                    <div class="input-group">
                        <span class="input-group-btn">
                            <button type="button" data-toggle="modal" data-target="#modal-search-config"
                                    id="search-config-btn" class="btn btn-flat btn-sm"><i class="fa fa-ellipsis-v"></i>
                            </button>
                        </span>
                        {!! Form::text('search', request('search'), ['id' =>'search', 'class' => 'form-control inline input-sm', 'placeholder' => trans('fi.search')]) !!}
                        <span class="input-group-btn">
                            <button type="submit" id="search-btn" class="btn btn-flat btn-sm input-sm"><i
                                        class="fa fa-search"></i></button>
                        </span>
                    </div>

                    <select id="task-filter" name="assignee" class="form-control inline input-sm">
                        <option value="" selected>{{ trans('fi.all_tasks') }}</option>
                        <option value="my_tasks">{{ trans('fi.my_tasks') }}</option>
                        <option value="assigned_from_others">{{ trans('fi.assigned_from_others') }}</option>
                    </select>

                    <select id="task-list-filter" name="status" class="form-control inline input-sm">
                        <option value="open" selected>{{ trans('fi.open') }}</option>
                        <option value="all">{{ trans('fi.all') }}</option>
                        <option value="closed">{{ trans('fi.closed') }}</option>
                        <option value="overdue">{{ trans('fi.overdue') }}</option>
                    </select>

                    {!! Form::text('date_range_filter', null, ['id' => 'date_range_filter', 'class' => 'form-control input-sm', 'placeholder' => trans('fi.date_range')]) !!}
                    {!! Form::hidden('date_range_filter_from', null, ['id' => 'date_range_filter_from', 'class' => 'form-control input-sm']) !!}
                    {!! Form::hidden('date_range_filter_to', null, ['id' => 'date_range_filter_to', 'class' => 'form-control input-sm']) !!}

                    @include('tasks.widget._tasks_search_config_modal')
                    {!! Form::close() !!}
                </div>
            </div>
            <div id="task-list-container">
                {{ trans('fi.loading') }}
            </div>
        </div>
    </div>

</div>