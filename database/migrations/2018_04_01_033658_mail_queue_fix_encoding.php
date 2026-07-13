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

class MailQueueFixEncoding extends Migration
{
    public function up()
    {
        foreach (DB::table('mail_queue')->where('to', 'like', '"%')->get() as $record)
        {
            DB::table('mail_queue')->where('id', $record->id)->update(['to' => json_encode([json_decode($record->to)])]);
        }

        foreach (DB::table('mail_queue')->where('cc', 'like', '"%')->get() as $record)
        {
            DB::table('mail_queue')->where('id', $record->id)->update(['cc' => json_encode([json_decode($record->to)])]);
        }

        foreach (DB::table('mail_queue')->where('bcc', 'like', '"%')->get() as $record)
        {
            DB::table('mail_queue')->where('id', $record->id)->update(['bcc' => json_encode([json_decode($record->to)])]);
        }
    }
}
