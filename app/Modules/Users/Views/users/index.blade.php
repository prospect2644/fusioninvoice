@extends('layouts.master')

@section('javascript')
    <script type="text/javascript">
        $(function () {

            alertify.dialog('userDeleteConfirm', function() {
              return {
                setup: function() {
                  var settings = alertify.confirm().settings;
                  for (var prop in settings)
                    this.settings[prop] = settings[prop];
                  var setup = alertify.confirm().setup();
                  setup.buttons.push({
                    text: '{{ trans('fi.make-user-inactive') }}',
                    key: 67,
                    scope: 'auxiliary',
                  });
                  return setup;
                },
                settings: {
                  oncontinue: null
                },
                callback: function(closeEvent) {
                  if (closeEvent.index == 2) {
                    if (typeof this.get('oncontinue') === 'function') {
                      returnValue = this.get('oncontinue').call(this, closeEvent);
                      if (typeof returnValue !== 'undefined') {
                        closeEvent.cancel = !returnValue;
                      }
                    }
                  } else {
                    alertify.confirm().callback.call(this, closeEvent);
                  }
                }
              };
            }, false, 'confirm');

            $('.user_filter_options').change(function () {
                $('form#filter').submit();
            });

            alertify.defaults.theme.ok = "ui negative button";
            alertify.defaults.theme.cancel = "ui black button";

            $("<style>").text(".ajs-header{ background-color: #ba0606 !important; }").appendTo($("body"));

            $('.delete-user').click(function () {

                var $_this = $(this);

                alertify.userDeleteConfirm("{!! trans('fi.delete_user_warning') !!}", function () {
                    window.location = decodeURIComponent($_this.data('action'));
                }, function () {
                    alertify.alert().destroy();
                }).set({
                  'oncontinue': function() {
                      window.location = decodeURIComponent($_this.data('inactive-action'));
                  },
                }).setHeader(confirmHeader).set({transition: 'zoom', defaultFocus: 'cancel'});

            });
        });
    </script>
@stop

@section('content')

    <section class="content-header">
        <h1 class="pull-left">
            {{ trans('fi.users') }}
        </h1>

        <div class="pull-right">
            <div class="btn-group">
                {!! Form::open(['method' => 'GET', 'id' => 'filter']) !!}
                {!! Form::select('userType', ['' => trans('fi.select-user-type')] + $allUserTypes, request('userType'), ['class' => 'user_filter_options form-control inline']) !!}
                {!! Form::close() !!}
            </div>

            <div class="btn-group">
                <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                    {{ trans('fi.new') }} <span class="caret"></span>
                </button>
                <ul class="dropdown-menu dropdown-menu-right">
                    @foreach($userTypes as $key => $value)
                        <li><a href="{{ route('users.create', [$key]) }}">{{ $value }}</a></li>
                    @endforeach
                </ul>
            </div>
        </div>
        <div class="clearfix"></div>
    </section>

    <section class="content">

        @include('layouts._alerts')

        <div class="row">

            <div class="col-xs-12">

                <div class="box box-primary">

                    <div class="box-body no-padding">
                        <table class="table table-hover table-striped">

                            <thead>
                            <tr>
                                <th>{!! Sortable::link('name', trans('fi.name')) !!}</th>
                                <th>{!! Sortable::link('email', trans('fi.email')) !!}</th>
                                <th>{{ trans('fi.type') }}</th>
                                <th>{{ trans('fi.last_login_at') }}</th>
                                <th>{{ trans('fi.status') }}</th>
                                <th>{{ trans('fi.options') }}</th>
                            </tr>
                            </thead>

                            <tbody>
                            @foreach ($users as $key => $user)
                                <tr>
                                    <td><a href="{{ route('users.edit', [$user->id, $user->user_type]) }}">{{ $user->name }}</a></td>
                                    <td>{{ $user->email }}</td>
                                    <td>{{ trans('fi.' . $user->user_type) }}</td>
                                    <td>{{ $user->formatted_last_login_at }}</td>
                                    <td>{!! $user->formatted_status !!}</td>
                                    <td>
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown">
                                                {{ trans('fi.options') }} <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-right">
                                                <li><a href="{{ route('users.edit', [$user->id, $user->user_type]) }}"><i class="fa fa-edit"></i> {{ trans('fi.edit') }}</a></li>
                                                <li><a href="{{ route('users.password.edit', [$user->id]) }}"><i class="fa fa-lock"></i> {{ trans('fi.reset_password') }}</a></li>
                                                @if($user->id !== auth()->user()->id)
                                                <li><a href="#" data-action="{{ route('users.delete', [$user->id])}}"
                                                       data-inactive-action="{{ route('users.update-status', [$user->id])}}"
                                                       class="delete-user text-danger"><i
                                                                class="fa fa-trash-o"></i> {{ trans('fi.delete') }}</a>
                                                </li>
                                                @endif
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>

                        </table>
                    </div>

                </div>

                <div class="pull-right">
                    {!! $users->appends(request()->except('page'))->render() !!}
                </div>

            </div>

        </div>

    </section>

@stop