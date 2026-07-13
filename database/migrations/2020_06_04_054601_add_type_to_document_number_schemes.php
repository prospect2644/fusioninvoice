<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddTypeToDocumentNumberSchemes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('document_number_schemes', function (Blueprint $table) {
            $table->enum('type', ['invoice', 'quote', 'credit_memo'])->after('name');
        });
        DB::table('document_number_schemes')->where('name', 'Invoice Default')->update(['type' => 'invoice']);
        DB::table('document_number_schemes')->where('name', 'Quote Default')->update(['type' => 'quote']);
        DB::table('document_number_schemes')->where('name', 'Credit Memo Default')->update(['type' => 'credit_memo']);
    }
}
