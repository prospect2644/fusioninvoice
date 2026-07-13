<script type="text/javascript">
    $(function () {

        $('.fi-colorpicker').colorpicker();

        $('#name').change(function () {
            let name = $(this).val().replace(/[^a-zA-Z ]/g, "").toLowerCase();
            let parts = name.split(' ');
            let initials = parts[0].substring(0, 1);

            if (2 > parts.length) {
                initials = initials + parts[parts.length - 1].substring(1, 2);
            } else {
                initials = initials + parts[parts.length - 1].substring(0, 1);
            }
            initials = initials.toUpperCase();

            $('#initials').val(initials);

            let colors = [
                "#1abc9c", "#2ecc71", "#3498db", "#9b59b6", "#34495e", "#16a085", "#27ae60", "#2980b9", "#8e44ad", "#2c3e50",
                "#f1c40f", "#e67e22", "#e74c3c", "#ecf0f1", "#95a5a6", "#f39c12", "#d35400", "#c0392b", "#bdc3c7", "#7f8c8d"
            ];
            let charIndex = initials.charCodeAt(0) + (initials.charCodeAt(1) || 0);
            let colorIndex = charIndex % 20;
            colorIndex = 0 >= colorIndex ? 1 : colorIndex;
            let color = colors[colorIndex - 1];
            let initialsBgColor = $('.initials-bg-color');
            initialsBgColor.val(color);
            initialsBgColor.closest('.colorpicker-element').find('.input-group-addon i').css({'background-color': color});
        });

    });
</script>