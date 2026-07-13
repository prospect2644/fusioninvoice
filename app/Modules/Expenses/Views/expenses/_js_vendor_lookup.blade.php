<script type="text/javascript">

    $(function () {

        // Define the select settings
        var settings = {
            placeholder: '{{ trans('fi.select-expense-vendor') }}',
            allowClear: true,
        };

        // Make all existing items select
        $('.vendor-lookup').select2(settings);

    });

</script>