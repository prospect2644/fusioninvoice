<div class="modal fade" id="modal-search-config" style="display: none;" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close close-search-config-modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">{{ trans('fi.note-search-config') }}</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="col-md-6">
                            <div class="form-group">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" class="search-config-chk" name="description" value="1" {{ !Session::has('filter_by_description') ? 'checked' : (Session::get('filter_by_description') == 1 ? 'checked' : '' )}}> {{ trans('fi.description') }}
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" class="search-config-chk" name="tags" value="1" {{ !Session::has('filter_by_tags') ? 'checked' : (Session::get('filter_by_tags') == 1 ? 'checked' : '')}}> {{ trans('fi.tags') }}
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" class="search-config-chk" name="username" value="1" {{ !Session::has('filter_by_username') ? 'checked' : (Session::get('filter_by_username') == 1 ? 'checked' : '')}}> {{ trans('fi.username') }}
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