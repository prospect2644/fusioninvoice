<div class="row">
    <div class="col-md-6">
        <div class="row">
            <div class="col-md-12" style="margin-top: 20px;">
                <div class="form-group">
                    @if(
                            !config('fi.clientTransitionHistoryCreated')
                            || !config('fi.expenseTransitionHistoryCreated')
                            || !config('fi.invoiceTransitionHistoryCreated')
                            || !config('fi.paymentInvoiceTransitionHistoryCreated')
                            || !config('fi.paymentTransitionHistoryCreated')
                            || !config('fi.quoteTransitionHistoryCreated')
                            || !config('fi.noteTransitionHistoryCreated')
                            || !config('fi.taskTransitionHistoryCreated')
                        )
                        <button type="button" class="btn btn-primary btn-sm" id="btn-generate-timeline"
                                data-loading-text="{{ trans('fi.generating_timeline_wait') }}"><i
                                    class="fa fa-clock-o"></i> {{ trans('fi.generate_timeline_history') }}</button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row">
    @if (!config('app.demo'))
        <div class="col-md-12">
            <table class="table">
                <thead>
                <tr>
                    <th>{{ trans('fi.item') }}</th>
                    <th>{{ trans('fi.value') }}</th>
                </tr>
                </thead>
                <tr>
                    <td class="view-field-label">{{trans('fi.client_transition')}}</td>
                    <td>{!! config('fi.clientTransitionHistoryCreated') ? trans('fi.created') : "<span class='label label-danger'>".trans('fi.pending')."</span>" !!}</td>
                </tr>
                <tr>
                    <td class="view-field-label">{{trans('fi.expense_transition')}}</td>
                    <td>{!! config('fi.expenseTransitionHistoryCreated') ? trans('fi.created') : "<span class='label label-danger'>".trans('fi.pending')."</span>" !!}</td>
                </tr>
                <tr>
                    <td class="view-field-label">{{trans('fi.invoice_transition')}}</td>
                    <td>{!! config('fi.invoiceTransitionHistoryCreated') ? trans('fi.created') : "<span class='label label-danger'>".trans('fi.pending')."</span>" !!}</td>
                </tr>
                <tr>
                    <td class="view-field-label">{{trans('fi.payment_invoice_transition')}}</td>
                    <td>{!! config('fi.paymentInvoiceTransitionHistoryCreated') ? trans('fi.created') : "<span class='label label-danger'>".trans('fi.pending')."</span>" !!}</td>
                </tr>
                <tr>
                    <td class="view-field-label">{{trans('fi.payment_transition')}}</td>
                    <td>{!! config('fi.paymentTransitionHistoryCreated') ? trans('fi.created') : "<span class='label label-danger'>".trans('fi.pending')."</span>" !!}</td>
                </tr>
                <tr>
                    <td class="view-field-label">{{trans('fi.quote_transition')}}</td>
                    <td>{!! config('fi.quoteTransitionHistoryCreated') ? trans('fi.created') : "<span class='label label-danger'>".trans('fi.pending')."</span>" !!}</td>
                </tr>
                <tr>
                    <td class="view-field-label">{{trans('fi.note_transition')}}</td>
                    <td>{!! config('fi.noteTransitionHistoryCreated') ? trans('fi.created') : "<span class='label label-danger'>".trans('fi.pending')."</span>" !!}</td>
                </tr>
                <tr>
                    <td class="view-field-label">{{trans('fi.task_transition')}}</td>
                    <td>{!! config('fi.taskTransitionHistoryCreated') ? trans('fi.created') : "<span class='label label-danger'>".trans('fi.pending')."</span>" !!}</td>
                </tr>

            </table>
        </div>
    @endif
</div>