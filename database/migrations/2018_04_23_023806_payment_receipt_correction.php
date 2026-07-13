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
use Illuminate\Support\Facades\DB;

class PaymentReceiptCorrection extends Migration
{
    public function up()
    {
        DB::table('mail_queue')->where('to', 'like', '{%')
            ->orWhere('cc', 'like', '{%')
            ->orWhere('bcc', 'like', '{%')
            ->delete();
    }
}
