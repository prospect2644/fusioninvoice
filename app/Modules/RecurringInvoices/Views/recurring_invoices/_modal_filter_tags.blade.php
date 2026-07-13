@include('layouts._typeahead')
<script type="text/javascript">
    (function () {
        @if($hasNoTags)
            let importantNoteHeader = '<span style="color:white;"> <span class="fa fa-exclamation-triangle fa-2x"'
            + 'style="vertical-align:middle;padding-right:10px;">'
            + '</span></span>';
            alertify.alert()
                    .setHeader(importantNoteHeader)
                    .setContent("{{ trans('fi.no_invoice_tags') }}")
                    .showModal();
        @else
            $('#modal-tags-filter').modal();
            $('#load-more-tags').click(function () {
                let $this = $(this);
                let link = $this.data('link');
                if (0 < link.length) {
                    $.get({
                        url: link,
                        success: function (response) {
                            $('#tags-filter-container').append(response.html);
                            $this.data('link', response.link);
                            $this.find('#next-page-count').text('[ + ] ' + response.nextPageCount);
                            if (0 >= response.link.length) {
                                $this.hide();
                            }
                        }
                    })
                }
            });

            $('#btn-submit-apply-tag-filter').click(function () {
                let $selectedTags = $('.filter-tag-chk:checked');
                let selectedTags = [];
                $selectedTags.each(function () {
                    selectedTags.push($(this).val());
                });
                let mustMatchAll = $('.must-match-all').is(':checked') ? 1 : 0;

                $('#tags-filter').val(JSON.stringify(selectedTags));
                $('#tags-must-match-all').val(mustMatchAll);
                $('#filter').submit();
            });

            $('#btn-clear-tag-filter').click(function () {
                $('#tags-filter').val('');
                $('#tags-must-match-all').val(0);
                $('#filter').submit();
            });
        @endif

    })();
</script>
@if(!$hasNoTags)
    <div class="modal fade" id="modal-tags-filter">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">{{ trans('fi.tags') }}</h4>
                </div>
                <div class="modal-body">

                    <div id="modal-status-placeholder"></div>

                    <div class="row">
                        <div id="tags-filter-container" style="max-height: 215px; margin-right: 15px; overflow-x: hidden; overflow-y: auto;">
                            @include('invoices._filter_tags_list', ['allTags' => $allTags, 'selectedTags' => $selectedTags])
                        </div>
                        @if($nextPageCount > 0)
                            <div class="col-md-3 col-md-offset-9">
                                <button type="button" class="btn btn-link" id="load-more-tags" style="padding-left: 0; padding-right: 0;" data-link="{{ $allTags->hasMorePages() ? $allTags->nextPageUrl() : '' }}">
                                    <span id="next-page-count">[ + ] {{ $nextPageCount }}</span> {{ trans('fi.more') }}
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="pull-left">
                        <div class="form-group">
                            <label>
                                {!! Form::checkbox('tagsMustMatchAll', $tagsMustMatchAll, $tagsMustMatchAll, ['class' => 'must-match-all']) !!}
                                {{ trans('fi.must_match_all') }}
                            </label>
                        </div>
                    </div>
                    <div class="pull-right">
                        <button type="button" class="btn btn-link" id="btn-clear-tag-filter">{{ trans('fi.clear') }}</button>
                        <button type="button" id="btn-submit-apply-tag-filter" class="btn btn-primary">{{ trans('fi.submit') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif