<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTypeFieldToInvoiceTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('invoices', function (Blueprint $table)
        {
            $table->enum('type', ['invoice', 'credit_memo'])->default('invoice')->after('status');
            DB::statement("ALTER TABLE " . DB::getTablePrefix() . "invoices MODIFY status ENUM('draft', 'sent', 'viewed','paid', 'canceled', 'unpaid', 'overdue','mailed','applied')");
            DB::table('document_number_schemes')
                ->insert([
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                    'name'       => 'Credit Memo Default',
                    'format'     => '{INVOICE_PREFIX} CR{YEAR}{NUMBER}',
                ]);
        });

        Schema::table('payments', function ($table)
        {
            $table->integer('credit_memo_id')->nullable();
            $table->integer('payment_method_id')->nullable()->change();
        });
    }
}

?>
