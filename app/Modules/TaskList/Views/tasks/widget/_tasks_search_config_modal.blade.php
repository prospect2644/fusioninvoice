<div class="modal fade" id="modal-search-config" style="display: none;">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close close-search-config-modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">{{ trans('fi.task-search-config') }}</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="col-md-6">
                            <div class="form-group">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" class="search-config-chk" name="filterBy[title]"
                                               value="1" {{ !Session::has('filter_by_title') ? 'checked' : (Session::get('filter_by_title') == 1 ? 'checked' : '' )}}> {{ trans('fi.title') }}
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" class="search-config-chk" name="filterBy[description]"
                                               value="1" {{ !Session::has('filter_by_task_description') ? 'checked' : (Session::get('filter_by_task_description') == 1 ? 'checked' : '')}}> {{ trans('fi.description') }}
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" class="search-config-chk" name="filterBy[client]"
                                               value="1" {{ !Session::has('filter_by_client') ? 'checked' : (Session::get('filter_by_client') == 1 ? 'checked' : '')}}> {{ trans('fi.client') }}
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" class="search-config-chk" name="filterBy[assignee]"
                                               value="1" {{ !Session::has('filter_by_assignee') ? 'checked' : (Session::get('filter_by_assignee') == 1 ? 'checked' : '')}}> {{ trans('fi.assignee') }}
                                    </label>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary custom-search" data-target="modal-placeholder">{{ trans('fi.save') }}</button>
            </div>
        </div>
    </div>
</div>