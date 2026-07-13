<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

Route::group(['prefix' => 'report', 'middleware' => ['web', 'auth.admin'], 'namespace' => 'FI\Modules\Reports\Controllers'], function ()
{
    Route::get('client_statement', ['uses' => 'ClientStatementReportController@index', 'as' => 'reports.clientStatement'])->middleware('can:client_statement.view');
    Route::post('client_statement/validate', ['uses' => 'ClientStatementReportController@validateOptions', 'as' => 'reports.clientStatement.validate'])->middleware('can:client_statement.view');
    Route::get('client_statement/html', ['uses' => 'ClientStatementReportController@html', 'as' => 'reports.clientStatement.html'])->middleware('can:client_statement.view');
    Route::get('client_statement/pdf', ['uses' => 'ClientStatementReportController@pdf', 'as' => 'reports.clientStatement.pdf'])->middleware('can:client_statement.view');

    Route::get('item_sales', ['uses' => 'ItemSalesReportController@index', 'as' => 'reports.itemSales'])->middleware('can:item_sales.view');
    Route::post('item_sales/validate', ['uses' => 'ItemSalesReportController@validateOptions', 'as' => 'reports.itemSales.validate'])->middleware('can:item_sales.view');
    Route::get('item_sales/html', ['uses' => 'ItemSalesReportController@html', 'as' => 'reports.itemSales.html'])->middleware('can:item_sales.view');
    Route::get('item_sales/pdf', ['uses' => 'ItemSalesReportController@pdf', 'as' => 'reports.itemSales.pdf'])->middleware('can:item_sales.view');

    Route::get('payments_collected', ['uses' => 'PaymentsCollectedReportController@index', 'as' => 'reports.paymentsCollected'])->middleware('can:payments_collected.view');
    Route::post('payments_collected/validate', ['uses' => 'PaymentsCollectedReportController@validateOptions', 'as' => 'reports.paymentsCollected.validate'])->middleware('can:payments_collected.view');
    Route::get('payments_collected/html', ['uses' => 'PaymentsCollectedReportController@html', 'as' => 'reports.paymentsCollected.html'])->middleware('can:payments_collected.view');
    Route::get('payments_collected/pdf', ['uses' => 'PaymentsCollectedReportController@pdf', 'as' => 'reports.paymentsCollected.pdf'])->middleware('can:payments_collected.view');

    Route::get('revenue_by_client', ['uses' => 'RevenueByClientReportController@index', 'as' => 'reports.revenueByClient'])->middleware('can:revenue_by_client.view');
    Route::post('revenue_by_client/validate', ['uses' => 'RevenueByClientReportController@validateOptions', 'as' => 'reports.revenueByClient.validate'])->middleware('can:revenue_by_client.view');
    Route::get('revenue_by_client/html', ['uses' => 'RevenueByClientReportController@html', 'as' => 'reports.revenueByClient.html'])->middleware('can:revenue_by_client.view');
    Route::get('revenue_by_client/pdf', ['uses' => 'RevenueByClientReportController@pdf', 'as' => 'reports.revenueByClient.pdf'])->middleware('can:revenue_by_client.view');

    Route::get('tax_summary', ['uses' => 'TaxSummaryReportController@index', 'as' => 'reports.taxSummary'])->middleware('can:tax_summary.view');
    Route::post('tax_summary/validate', ['uses' => 'TaxSummaryReportController@validateOptions', 'as' => 'reports.taxSummary.validate'])->middleware('can:tax_summary.view');
    Route::get('tax_summary/html', ['uses' => 'TaxSummaryReportController@html', 'as' => 'reports.taxSummary.html'])->middleware('can:tax_summary.view');
    Route::get('tax_summary/pdf', ['uses' => 'TaxSummaryReportController@pdf', 'as' => 'reports.taxSummary.pdf'])->middleware('can:tax_summary.view');

    Route::get('profit_loss', ['uses' => 'ProfitLossReportController@index', 'as' => 'reports.profitLoss'])->middleware('can:profit_and_loss.view');
    Route::post('profit_loss/validate', ['uses' => 'ProfitLossReportController@validateOptions', 'as' => 'reports.profitLoss.validate'])->middleware('can:profit_and_loss.view');
    Route::get('profit_loss/html', ['uses' => 'ProfitLossReportController@html', 'as' => 'reports.profitLoss.html'])->middleware('can:profit_and_loss.view');
    Route::get('profit_loss/pdf', ['uses' => 'ProfitLossReportController@pdf', 'as' => 'reports.profitLoss.pdf'])->middleware('can:profit_and_loss.view');

    Route::get('expense_list', ['uses' => 'ExpenseListReportController@index', 'as' => 'reports.expenseList'])->middleware('can:expense_list.view');
    Route::post('expense_list/validate', ['uses' => 'ExpenseListReportController@validateOptions', 'as' => 'reports.expenseList.validate'])->middleware('can:expense_list.view');
    Route::get('expense_list/html', ['uses' => 'ExpenseListReportController@html', 'as' => 'reports.expenseList.html'])->middleware('can:expense_list.view');
    Route::get('expense_list/pdf', ['uses' => 'ExpenseListReportController@pdf', 'as' => 'reports.expenseList.pdf'])->middleware('can:expense_list.view');

    Route::get('recurring_invoice_list', ['uses' => 'RecurringInvoiceReportController@index', 'as' => 'reports.recurringInvoiceList'])->middleware('can:recurring_invoice_list.view');
    Route::post('recurring_invoice_list/validate', ['uses' => 'RecurringInvoiceReportController@validateOptions', 'as' => 'reports.recurringInvoiceList.validate'])->middleware('can:recurring_invoice_list.view');
    Route::get('recurring_invoice_list/html', ['uses' => 'RecurringInvoiceReportController@html', 'as' => 'reports.recurringInvoiceList.html'])->middleware('can:recurring_invoice_list.view');
    Route::get('recurring_invoice_list/pdf', ['uses' => 'RecurringInvoiceReportController@pdf', 'as' => 'reports.recurringInvoiceList.pdf'])->middleware('can:recurring_invoice_list.view');

    Route::get('client_invoice', ['uses' => 'ClientInvoiceReportController@index', 'as' => 'reports.clientInvoice'])->middleware('can:client_invoice.view');
    Route::post('client_invoice/validate', ['uses' => 'ClientInvoiceReportController@validateOptions', 'as' => 'reports.clientInvoice.validate'])->middleware('can:client_invoice.view');
    Route::get('client_invoice/html', ['uses' => 'ClientInvoiceReportController@html', 'as' => 'reports.clientInvoice.html'])->middleware('can:client_invoice.view');
    Route::get('client_invoice/pdf', ['uses' => 'ClientInvoiceReportController@pdf', 'as' => 'reports.clientInvoice.pdf'])->middleware('can:client_invoice.view');
});