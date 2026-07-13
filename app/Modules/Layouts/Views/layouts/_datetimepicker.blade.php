<script src='{{ asset('assets/plugins/daterangepicker/moment.js') }}'></script>
<script src='{{ asset('assets/plugins/datetimepicker/bootstrap-datetimepicker.min.js') }}'></script>
<link href="{{ asset('assets/plugins/datetimepicker/bootstrap-datetimepicker.min.css') }}" rel="stylesheet"
      type="text/css"/>
<script>
    $(function () {
        $.fn.datetimepicker.defaults.format = "{{ $dateTimeFormat }}";
    });
</script>