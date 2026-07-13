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
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class DefaultCustomRecords extends Migration
{
    public function up()
    {
        //Insert missing client custom records
        $clients = DB::table('clients')->whereNotIn('id', function ($query)
        {
            $query->select('client_id')->from('clients_custom');
        })->get();

        foreach ($clients as $client)
        {
            DB::table('clients_custom')->insert([
                'client_id'  => $client->id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }

        //Insert missing quotes custom records
        $quotes = DB::table('quotes')->whereNotIn('id', function ($query)
        {
            $query->select('quote_id')->from('quotes_custom');
        })->get();

        foreach ($quotes as $quote)
        {
            DB::table('quotes_custom')->insert([
                'quote_id'   => $quote->id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }

        //Insert missing invoice custom records
        $invoices = DB::table('invoices')->whereNotIn('id', function ($query)
        {
            $query->select('invoice_id')->from('invoices_custom');
        })->get();

        foreach ($invoices as $invoice)
        {
            DB::table('invoices_custom')->insert([
                'invoice_id' => $invoice->id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }

        //Insert missing payments custom records
        $payments = DB::table('payments')->whereNotIn('id', function ($query)
        {
            $query->select('payment_id')->from('payments_custom');
        })->get();

        foreach ($payments as $payment)
        {
            DB::table('payments_custom')->insert([
                'payment_id' => $payment->id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }

        //Insert missing users custom records
        $users = DB::table('users')->whereNotIn('id', function ($query)
        {
            $query->select('user_id')->from('users_custom');
        })->get();

        foreach ($users as $user)
        {
            DB::table('users_custom')->insert([
                'user_id'    => $user->id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }
    }
}
