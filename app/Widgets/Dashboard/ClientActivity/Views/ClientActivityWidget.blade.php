@can('recent_client_activity.view')
<div id="client-activity-widget">

    <div class="box box-solid">
        <div class="box-header">
            <h3 class="box-title">{{ trans('fi.recent_client_activity') }}</h3>
        </div>
        <div class="box-body">
            <table class="table table-striped">
                <tbody>
                <tr>
                    <th>{{ trans('fi.date') }}</th>
                    <th>{{ trans('fi.activity') }}</th>
                </tr>
                @foreach ($recentClientActivity as $activity)
                    <tr>
                        <td>{{ $activity->formatted_created_at }}</td>
                        <td>{!! $activity->formatted_activity !!}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>
@endcan