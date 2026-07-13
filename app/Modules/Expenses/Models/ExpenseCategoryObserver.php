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

class ExpenseCategoryObserver
{
    public function deleted(ExpenseCategory $expenseCategory)
    {
        Expense::where('category_id', $expenseCategory->id)->update(['category_id' => 0]);
    }
}