<script type="text/javascript">
    function chosenEmailField(id) {
        $(id).chosen().parent().find('.chosen-container .search-field input[type=text]').keydown(function (evt) {
            // get keycode
            const stroke = evt.which != null ? evt.which : evt.keyCode;
            // If enter or tab key
            if (stroke === 9 || stroke === 13) {
                const target = $(evt.target);
                // get the list of current options
                const chosenList = target.parents('.chosen-container').find('.chosen-choices li.search-choice > span').map(function () {
                    return $(this).text();
                }).get();
                // get the list of matches from the existing drop-down
                const matchList = target.parents('.chosen-container').find('.chosen-results li').map(function () {
                    return $(this).text();
                }).get();
                // highlighted option
                const highlightedList = target.parents('.chosen-container').find('.chosen-results li.highlighted').map(function () {
                    return $(this).text();
                }).get();
                // Get the value which the user has typed in
                const newString = $.trim(target.val());
                // if the option does not exists, and the text doesn't exactly match an existing option, and there is not an option highlighted in the list
                if ($.inArray(newString, matchList) < 0 && $.inArray(newString, chosenList) < 0 && highlightedList.length == 0) {
                    // Before inserting new email, we have to validate it.
                    var emailRegEx = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
                    if (emailRegEx.test(newString)) {
                        // Create a new option and add it to the list (but don't make it selected)
                        const newOption = '<option value="' + newString + '" selected="selected">' + newString + '</option>';
                        const choiceSelect = target.parents('.chosen-container').siblings(id);
                        choiceSelect.append(newOption);
                        // trigger the update event
                        choiceSelect.trigger("chosen:updated");
                        // tell chosen to close the list box
                        choiceSelect.trigger("chosen:close");
                    }
                    return true;
                }
                // otherwise, just let the event bubble up
                return true;
            }
        });
    }
</script>