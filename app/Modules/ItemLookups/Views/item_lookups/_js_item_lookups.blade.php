<script type="text/javascript">

    $(function () {

        select2ItemSelect();
        select2Init();

        //select2 init
        function select2Init() {
            // Define the select settings
            var settings = {
                placeholder: '{{ trans('fi.select-item') }}',
                allowClear: true,
                tags: true,
                selectOnClose: true
            };

            // Make all existing items select
            $('.item-lookup').select2(settings);
        }

        // Sets up .item-lookup to populate proper fields when item is selected
        function select2ItemSelect() {
            $('.item-lookup').on('select2:select', function (e) {
                if (typeof e.params.data.element !== 'undefined') {
                    var row = $(this).closest('tr');
                    $.ajax({
                        url: "{{ route('itemLookups.getDetail') }}",
                        method: 'post',
                        data: {id: $(this).val()},
                        beforeSend: function () {
                            $(".modal-loader").show();
                        },
                        success: function (data) {
                            $(".modal-loader").hide();
                            row.find('textarea[name="description"]').val(data.description);
                            row.find('input[name="quantity"]').val(((data.invoice_type == 'credit_memo') ? '-1' : '1'));
                            row.find('input[name="price"]').val(data.price);
                            row.find('select[name="tax_rate_id"]').val(data.tax_rate_id == -1 ? '{{ config('fi.itemTaxRate') }}' : data.tax_rate_id);
                            row.find('select[name="tax_rate_2_id"]').val(data.tax_rate_2_id == -1 ? '{{ config('fi.itemTax2Rate') }}' : data.tax_rate_2_id);
                            row.find('.lbl_item_lookup').hide();
                        }
                    });
                } else {
                    var row = $(this).closest('tr');
                    row.find('textarea[name="description"]').val('');
                    row.find('input[name="quantity"]').val('1');
                    row.find('input[name="price"]').val('');
                    row.find('select[name="tax_rate_id"]').val('{{ config('fi.itemTaxRate') }}');
                    row.find('select[name="tax_rate_2_id"]').val('{{ config('fi.itemTax2Rate') }}');
                    row.find('.lbl_item_lookup').show();
                }
            });
        }

        // Clones a new item row
        function cloneItemRow(initialLoad) {
            var row = $('#new-item').clone().appendTo('#item-table');
            row.removeAttr('id').addClass('item').show();
            row.find('select[name="name"]').addClass('item-lookup');
            $('textarea').autosize();
            if (initialLoad == true) {
                row.find('.btn-danger').remove();
            }
            select2Init();
            select2ItemSelect();
        }

        $(document).on('click', '#btn-add-item', function () {
            cloneItemRow(false);
        });

        // Add a new item row if no items currently exist
        @if (!$itemCount)
        cloneItemRow(true);
        @endif






    });

</script>