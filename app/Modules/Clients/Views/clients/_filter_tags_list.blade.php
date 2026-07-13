@foreach($checkedTags as $tag)
    <div class="col-md-6 tag-list-item">
        <div class="form-group filter-tag-item">
            <label>
                {{ Form::checkbox('tags[' . $tag->id . ']', $tag->id, true, ['class' => 'filter-tag-chk']) }}
                {{ $tag->name }}
            </label>
        </div>
    </div>
@endforeach
@foreach($allTags as $tag)
    <div class="col-md-6 tag-list-item">
        <div class="form-group filter-tag-item">
            <label>
                {{ Form::checkbox('tags[' . $tag->id . ']', $tag->id, false, ['class' => 'filter-tag-chk']) }}
                {{ $tag->name }}
            </label>
        </div>
    </div>
@endforeach