<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Modules\Expenses\Controllers;

use FI\Http\Controllers\Controller;
use FI\Modules\Expenses\Models\Expense;
use FI\Modules\Expenses\Requests\ExpenseBillRequest;
use FI\Modules\Invoices\Models\InvoiceItem;
use FI\Modules\Expenses\Events\AddTransition;

class ExpenseBillController extends Controller
{
    public function create()
    {
        $expense = Expense::defaultQuery()->find(request('id'));

        $clientInvoices = $expense->client->invoices()->orderBy('created_at', 'desc')->statusIn([
            'draft',
            'sent',
        ])->get();

        $invoices = [];

        foreach ($clientInvoices as $invoice)
        {
            $invoices[$invoice->id] = $invoice->formatted_created_at . ' - ' . $invoice->number . ' ' . $invoice->summary;
        }

        return view('expenses._modal_bill')
            ->with('expense', $expense)
            ->with('invoices', $invoices)
            ->with('redirectTo', request('redirectTo'));
    }

    public function store(ExpenseBillRequest $request)
    {
        $expense = Expense::find($request->input('id'));

        $expense->invoice_id = $request->input('invoice_id');

        $expense->save();

        if ($request->input('add_line_item'))
        {
            $item = [
                'invoice_id'  => $request->input('invoice_id'),
                'name'        => $request->input('item_name'),
                'description' => $request->input('item_description'),
                'quantity'    => 1,
                'price'       => $expense->amount,
            ];

            InvoiceItem::create($item);
            event(new AddTransition($expense, 'billed'));
        }
    }
}