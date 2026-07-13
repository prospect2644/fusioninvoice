@can('client_timeline.view')
<div id="client_timeline-widget">
    @include('layouts._select2')
    @include('layouts._bootstrap-multiselect')
    <script type="text/javascript">
        $(function () {

            function loadTimelineList(page=1) {
                let $form = $('#transitions-filter-form');
                let data = $form.serializeArray();
                data.push({name: 'page', value: page});
                let passUrl = "{{route('transitions.widget.list')}}"

                $.ajax({
                    url: passUrl,
                    method: 'post',
                    data: data,
                    beforeSend: function () {
                        $(".modal-loader").show();
                    },
                    success: function (response) {
                        $(".modal-loader").hide();
                        $('#timeline-container').html(response);
                    },
                    error: function () {
                        $(".modal-loader").hide();
                        alertify.error('{{ trans('fi.unknown_error') }}', 5);
                    }
                });
            }

            $('#selectUser').on('change', function () {
                return loadTimelineList();
            });

            $("#transitions-filter-form").submit(function (e) {
                e.preventDefault();
                return loadTimelineList();
            });

            $('#selectUser').select2({
                placeholder: "{{ trans('fi.select_user') }}",
                dropdownAutoWidth: true
            });

            $('#entity-selection').multiselect({
                buttonText: function () {
                    return "{!! trans('fi.select_event') !!}";
                },
                onChange: function () {
                    return loadTimelineList();
                },
                buttonClass: 'btn btn-sm btn-default',
                enableHTML: true,
                inheritClass: true,
                buttonWidth: 'auto',
                buttonContainer: '<div class="btn-group" />',
                selectedClass: 'active',
                enableClickableOptGroups: true,
                enableCollapsibleOptGroups: true,
                collapseOptGroupsByDefault: true
            });
            $('#reset-transition-btn').on('click', function () {

                $('#entity-selection option:selected').each(function () {
                    $(this).prop('selected', false);
                });

                $('#entity-selection').multiselect('refresh');
                $('#custom_search').val('');
                $('#selectUser').val(null).trigger('change');
            })

            $(document).on('click', '.pagination a', function (event) {
                event.preventDefault();
                var page = $(this).attr('href').split('page=')[1];
                loadTimelineList(page);
            });

            loadTimelineList();
        });
    </script>
    <style>
        .form-control, .select2-container {
            margin-bottom: 5px;
        }
    </style>
    <div class="box box-solid transitions-list">
        <div class="box-header with-border">
            <h3 class="fa fa-clock-o"></h3>

            <h3 class="box-title">{{ trans('fi.timeline') }}</h3>

        </div>
        <div class="box-body">
            <div class="row">
                <div class="col-md-12">
                    @if(!empty($filterUsers))
                        <div class="pull-right" style="margin-right: 2%;">
                            {!! Form::open(['method' => 'GET', 'id' => 'transitions-filter-form', 'class' => 'form-inline']) !!}
                            <div class="form-group">
                                {!! Form::select('user[]', $filterUsers, null, ['multiple' => 'multiple','id' => 'selectUser','class' => 'form-control input-sm', 'style'=>"width: 300px;"]) !!}
                                {!! Form::select('filter_module[]', $modules, null, ['id' => 'entity-selection','multiple' => 'multiple','class' => 'form-control multiselect input-sm']) !!}
                                {!! Form::text('custom_search', request('search'), ['id' =>'custom_search', 'class' => 'form-control inline input-sm', 'placeholder' => trans('fi.search')]) !!}
                                <button type="submit" name="search" id="filter-transition-btn"
                                        class="btn btn-sm btn-primary"
                                        title="{{trans('fi.search')}}"><i class="fa fa-search"></i></button>
                                <button type="button" id="reset-transition-btn" class="btn btn-sm btn-primary"
                                        title="{{trans('fi.reset')}}"><i class="fa fa-refresh" aria-hidden="true"></i>
                                </button>
                            </div>
                            {!! Form::close() !!}
                        </div>
                    @endif
                </div>
            </div>
            <div id="timeline-container"></div>
        </div>
    </div>
</div>
@endcan
<style>
    .transitions-list .timeline > li > .timeline-item {
        margin: 10px 0 0 0;
        float: left;
        width: 93%;
        background-color: #ecf0f5;
    }

    .transitions-list .timeline > li > .fa, .transitions-list .timeline > li > .glyphicon, .transitions-list .timeline > li > .ion {
        left: 23px
    }

    .transitions-list .badge {
        background: none;
        margin-left: 10px;
        float: left;
    }

    .transitions-list .timeline > li > .timeline-item > .timeline-header > .title {
        color: #17abe6;
        font-weight: 600;
    }

    .transitions-list .timeline > li > .timeline-item > .timeline-header {
        border-bottom: 1px solid #fff;
    }
</style>