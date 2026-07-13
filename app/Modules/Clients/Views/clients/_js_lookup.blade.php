<script type="text/javascript">
    $(function () {
        // Define the select settings
        var settings = {
            placeholder: '{{ trans('fi.select_client') }}',
            allowClear: true
        };

        // Make all existing items select
        $('.client-lookup').select2(settings);
    });
</script>