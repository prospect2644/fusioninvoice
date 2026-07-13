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
use FI\Modules\Attachments\Models\Attachment;
use FI\Modules\Expenses\Models\Expense;
use FI\Modules\Expenses\Requests\ExpenseCopyRequest;
use Carbon\Carbon;

class ExpenseCopyController extends Controller
{

    public function store(ExpenseCopyRequest $request)
    {

        $fromExpense = Expense::find($request->input('expense_id'));

        $toExpense = Expense::create([
            'expense_date'       => Carbon::now()->format('Y-m-d'),
            'user_id'            => $fromExpense->user_id,
            'category_id'        => $fromExpense->category_id,
            'vendor_id'          => $fromExpense->vendor_id,
            'invoice_id'         => $fromExpense->invoice_id,
            'description'        => $fromExpense->description,
            'amount'             => $fromExpense->amount,
            'tax'                => $fromExpense->tax,
            'company_profile_id' => $fromExpense->company_profile_id,
        ]);

        // Copy the custom fields
        $custom = collect($fromExpense->custom)->except('expense_id')->toArray();
        $toExpense->custom->update($custom);

        // Copy attachments
        foreach ($fromExpense->attachments as $attachment)
        {
            Attachment::create([
                'user_id'           => $attachment->user_id,
                'attachable_id'     => $toExpense->id,
                'attachable_type'   => $attachment->attachable_type,
                'filename'          => $attachment->filename,
                'mimetype'          => $attachment->mimetype,
                'size'              => $attachment->size,
                'client_visibility' => $attachment->client_visibility,
                'content'           => $attachment->content,
            ]);
        }

        return response()->json(['id' => $toExpense->id], 200);
    }
}