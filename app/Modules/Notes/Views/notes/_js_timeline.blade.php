<script type="text/javascript">
    $(function () {
        $('#note-timeline-container').load('{{ route('notes.list', [base64_encode($model), $object->id, $showPrivateCheckbox, 'description' => !Session::has('filter_by_description') ? '1' : (Session::get('filter_by_description') == 1 ? '1' : '0'), 'tags' => !Session::has('filter_by_tags') ? '1' : (Session::get('filter_by_tags') == 1 ? '1' : '0'), 'username' => !Session::has('filter_by_username') ? '1' : (Session::get('filter_by_username') == 1 ? '1' : '0') ]) }}');
        $('body').on('click', '#notes-pagination a', function (e) {
            e.preventDefault();

            $('#note-timeline-container').load($(this).attr('href'));
        });
    });
</script>