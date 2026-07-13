<style>
    .notification-table {
        max-height: 380px;
        overflow-y: scroll;
        overflow-x: hidden;
    }

    ::-webkit-scrollbar {
        -webkit-appearance: none;
        width: 7px;
    }

    ::-webkit-scrollbar-thumb {
        border-radius: 4px;
        background-color: rgba(0, 0, 0, .5);
        box-shadow: 0 0 1px rgba(255, 255, 255, .5);
    }
</style>
<script type="text/javascript">
    $(function () {
        $('#modal-notifications').modal();
        $('.notification-item').click(function () {
            var url = '{{ route("notifications.markViewed", ":notification") }}';
            url = url.replace(':notification', $(this).data('notification-id'));
            var redirect_url = $(this).data('url');
            $.post(url, function () {
                window.location = redirect_url;
            });
        })
    });
</script>
