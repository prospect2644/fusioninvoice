<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use FI\Modules\Clients\Models\Client;
use FI\Modules\Clients\Support\ClientInvoicePrefixGenerator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AlterClientsForInvoicePrefix extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('clients', function (Blueprint $table)
        {
            $table->string('invoice_prefix');
        });

        //Lets add default prefix for existing clients
        $clients                      = Client::all();
        $clientInvoicePrefixGenerator = new ClientInvoicePrefixGenerator();
        foreach ($clients as $client)
        {
            do
            {
                $invoicePrefix = $clientInvoicePrefixGenerator->invoicePrefixGenerator();
            } while ($clientInvoicePrefixGenerator->isUnique($invoicePrefix));

            DB::table('clients')->where('id', $client->id)->update(['invoice_prefix' => $invoicePrefix]);
        }

        // lets set invoice prefix unique field
        DB::statement("ALTER TABLE " . DB::getTablePrefix() . "clients ADD UNIQUE(`invoice_prefix`)");
    }

}
