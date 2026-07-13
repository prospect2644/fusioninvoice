@include('layouts._select2')
@include('layouts._bootstrap-multiselect')
<script type="text/javascript">
    $(function () {

        $.fn.loadTimelineList = function (page=1) {
            let $form = $('#transitions-filter-form');
            let data = $form.serializeArray();
            data.push({name: 'page', value: page});
            let passUrl = "{{route("transitions.user-list",['client' => $clientId])}}"

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
            return $.fn.loadTimelineList();
        });

        $("#transitions-filter-form").submit(function (e) {
            e.preventDefault();
            return $.fn.loadTimelineList();
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
                return $.fn.loadTimelineList();
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

        $('#client-create-note-transition').click(function () {
            $('#note-modal-placeholder').load('{{ route('notes.create') }}');
        });

        $(document).on('click', '.pagination a', function (event) {
            event.preventDefault();
            var page = $(this).attr('href').split('page=')[1];
            $.fn.loadTimelineList(page);
        });

        $(document).on('click', '#btn-clear-transition-filter, #reset-transition-btn', function (event) {
            event.preventDefault();
            $('#entity-selection option:selected').each(function () {
                $(this).prop('selected', false);
            });

            $('#entity-selection').multiselect('refresh');
            $('#custom_search').val('');
            $('#selectUser').val([]).trigger('change');
        });

        $.fn.loadTimelineList();

        $('.note-collapsed').click(function () {
            if ($(this).attr('aria-expanded') == 'false') {
                var text = '{{ trans("fi.show_less") }}';
            } else {
                var text = '{{ trans("fi.show_more") }}';
            }
            $(this).text(text);
        });
    });
</script>
<div class="row transitions-list">

    <div class="col-xs-12">
        <div class="col-md-3 pull-left">
            <h2 style="display:inline;"><i class="fa fa-clock-o"></i> {{ trans('fi.timeline') }}</h2>
            <span class="label label-info transition-count" style="margin-left: 8px;"></span>
        </div>
        <div class="col-md-9 text-right pd-rt-5">

            {!! Form::open(['method' => 'GET', 'url' => route('transitions.user-list',['client' => $clientId]), 'id' => 'transitions-filter-form', 'class' => 'form-horizontal']) !!}
            <div class="form-group">
                {!! Form::select('user[]', $filterUsers, null, ['multiple' => 'multiple','id' => 'selectUser','class' => 'form-control input-sm', 'style'=>"width: 300px;"]) !!}
                {!! Form::select('filter_module[]', $modules, null, ['id' => 'entity-selection','multiple' => 'multiple']) !!}
                {!! Form::text('custom_search', request('search'), ['id' =>'custom_search', 'class' => 'form-control inline input-sm', 'placeholder' => trans('fi.search')]) !!}
                <button type="submit" name="search" id="filter-transition-btn"
                        class="btn btn-sm btn-primary"
                        title="{{trans('fi.search')}}"><i class="fa fa-search"></i></button>
                <button type="button" id="reset-transition-btn" class="btn btn-sm btn-primary"
                        title="{{trans('fi.reset')}}"><i class="fa fa-refresh" aria-hidden="true"></i>
                </button>
                @can('notes.create')
                <a href="javascript:void(0)" class="btn btn-default pull-right"
                   style="margin-right: 15px; margin-top: 5px;" id="client-create-note-transition"><i
                            class="fa fa-comments-o" style="padding-right:4px;"></i> {{ trans('fi.add_note') }}</a>
                @endcan
            </div>
            {!! Form::close() !!}
        </div>

    </div>

    <div class="col-xs-12">
        <div id="timeline-container"></div>
    </div>


</div>
<style>
    .transitions-list .timeline > li > .timeline-item {
        margin: 10px 0 0 0;
        float: left;
        width: 96%;
    }

    .transitions-list .timeline > li > .fa, .transitions-list .timeline > li > .glyphicon, .transitions-list .timeline > li > .ion {
        left: 23px
    }

    .transitions-list .timeline > li > .timeline-item {
        margin-left: 5px;
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

    .form-control, .select2-container {
        margin-bottom: 5px;
    }

</style>