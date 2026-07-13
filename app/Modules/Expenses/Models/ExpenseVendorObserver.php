<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Modules\Expenses\Models;

class ExpenseVendorObserver
{
    public function deleted(ExpenseVendor $expenseVendor)
    {
        Expense::where('vendor_id', $expenseVendor->id)->update(['vendor_id' => 0]);
    }
}