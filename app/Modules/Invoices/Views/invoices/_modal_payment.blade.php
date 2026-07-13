@include('layouts._datepicker')
@include('payments._js_form')
<script type="text/javascript">
    $(function () {
        var modalPayment = $('#modal-payment');

        modalPayment.modal();

        $('#btn-payment-submit').click(function () {
            var custom_fields = {};
            var payment_data = {};
            var files = [];
            var form_data;

            var $btn = $(this).button('loading');

            $('.custom-form-field').each(function () {
                var fieldName = $(this).data('payments-field-name');
                var inputType = $(this).attr('type') || this.tagName.toLowerCase();
                if (fieldName !== undefined) {
                    if ('file' === inputType) {
                        custom_fields[fieldName] = typeof this.files[0] === 'undefined' ? '' : this.files[0];

                        return true;
                    }

                    if ('select' === inputType) {
                        if ($(this).find('option:selected').length == 0) {
                            custom_fields[fieldName] = '';
                            return true;
                        }
                    }

                    if ('checkbox' === inputType) {
                        custom_fields[fieldName] = ($(this).is(":checked")) ? 1 : 0;
                        return true;
                    }

                    custom_fields[fieldName] = $(this).val();
                }
            });

            payment_data['amount'] = $('#amount').val();
            payment_data['paid_at'] = $('#paid_at').val();
            payment_data['payment_method_id'] = $('#payment_method_id').val();
            payment_data['note'] = $('#note').val();
            payment_data['invoice_id'] = '{{ $invoice->id }}';
            payment_data['custom'] = custom_fields;
            payment_data['custom_files'] = files;

            form_data = objectToFormData(payment_data);
            $.ajax({
                url: '{{ $submitRoute }}',
                method: 'post',
                data: form_data,
                processData: false,
                contentType: false
            }).done(function (response) {
                modalPayment.modal('hide');
                $('#tab-payments').html(response);
                $('#div-totals').load('{{ route('invoiceEdit.refreshTotals') }}', {
                    id: '{{ $invoice->id }}'
                });
                alertify.success('{{ trans('fi.record_successfully_updated') }}', 5);
            }).fail(function (response) {
                $btn.button('reset');
                $.each($.parseJSON(response.responseText).errors, function (id, message) {
                    alertify.error(message[0], 5);
                });
            });
        });

        $('#btn-delete-custom-img').click(function () {
            var url = "{{ route('payments.deleteImage', [$payment->id,'field_name' => '']) }}";
            $.post(url + '/' + $(this).data('field-name')).done(function () {
                $('.custom_img').html('');
            });
        });

    });
</script>

<div class="modal" id="modal-payment">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">
                    {{ trans('fi.payment_form') }}
                </h4>
            </div>
            <div class="modal-body">

                <div id="modal-status-placeholder"></div>

                {!! Form::model($payment, ['route' => ['payments.update', $payment->id], 'class' => 'form-horizontal']) !!}

                <section class="content">

                    <div class="form-group">
                        <label class="col-sm-4 control-label">{{ trans('fi.amount') }}: </label>

                        <div class="col-sm-8">
                            {!! Form::text('amount', $payment->formatted_numeric_amount, ['id' => 'amount',
                            'class' => 'form-control']) !!}
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-4 control-label">{{ trans('fi.payment_date') }}: </label>

                        <div class="col-sm-8">
                            {!! Form::text('paid_at', $payment->formatted_paid_at, ['id' => 'paid_at', 'class'
                            => 'form-control']) !!}
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-4 control-label">{{ trans('fi.payment_method') }}</label>

                        <div class="col-sm-8">
                            {!! Form::select('payment_method_id', $paymentMethods, null, ['id' =>
                            'payment_method_id', 'class' => 'form-control']) !!}
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-4 control-label">{{ trans('fi.note') }}</label>

                        <div class="col-sm-8">
                            {!! Form::textarea('note', null, ['id' => 'note', 'class' => 'form-control', 'rows'=>'3', 'cols'=>'50']) !!}
                        </div>
                    </div>

                    @if ($customFields)
                        @include('custom_fields._custom_fields_modal', ['object' => isset($payment) ? $payment : []])
                    @endif

                </section>

                {!! Form::hidden('invoice_id') !!}

                {!! Form::close() !!}

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('fi.cancel') }}</button>
                <button type="button" id="btn-payment-submit" class="btn btn-primary">{{ trans('fi.save') }}</button>
            </div>
        </div>
    </div>
</div>