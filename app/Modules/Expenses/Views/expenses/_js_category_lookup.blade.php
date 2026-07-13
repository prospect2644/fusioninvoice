<script type="text/javascript">

    $(function () {
        // Define the select settings
        var settings = {
            placeholder: '{{ trans('fi.select-expense-category') }}',
            allowClear: true,
        };

        // Make all existing items select
        $('.category-lookup').select2(settings);
    });

</script>