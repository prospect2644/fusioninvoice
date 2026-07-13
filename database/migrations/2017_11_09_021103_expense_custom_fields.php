<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use FI\Modules\Expenses\Models\Expense;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ExpenseCustomFields extends Migration
{
    public function up()
    {
        Schema::create('expenses_custom', function (Blueprint $table)
        {
            $table->integer('expense_id');
            $table->timestamps();

            $table->primary('expense_id');
        });

        foreach (Expense::get() as $expense)
        {
            DB::table('expenses_custom')
                ->insert([
                    'created_at' => $expense->created_at,
                    'updated_at' => $expense->updated_at,
                    'expense_id' => $expense->id,
                ]);
        }
    }
}
