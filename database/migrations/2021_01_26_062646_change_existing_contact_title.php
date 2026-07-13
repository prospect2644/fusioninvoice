<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Illuminate\Database\Migrations\Migration;

class ChangeExistingContactTitle extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $contacts = DB::table('contacts')->get();

        foreach ($contacts as $contact)
        {
            if ($contact->title != null)
            {
                DB::table('contacts')->where('id', $contact->id)->update(['title' => strtolower(str_replace(".", "", $contact->title))]);
            }
        }
    }
}