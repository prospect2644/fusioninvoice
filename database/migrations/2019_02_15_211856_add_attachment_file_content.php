<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use FI\Modules\Attachments\Models\Attachment;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddAttachmentFileContent extends Migration
{
    public function up()
    {
        DB::statement('ALTER TABLE ' . DB::getTablePrefix() . 'attachments ADD content LONGBLOB');

        foreach (Attachment::get() as $attachment)
        {
            $attachmentPath = null;

            switch (class_basename($attachment->attachable_type))
            {
                case 'Invoice':
                    $attachmentPath = 'attachments/invoices/' . $attachment->attachable_id;
                    break;
                case 'Quote':
                    $attachmentPath = 'attachments/quotes/' . $attachment->attachable_id;
                    break;
                case 'Client':
                    $attachmentPath = 'attachments/clients/' . $attachment->attachable_id;
                    break;
                case 'Expense':
                    $attachmentPath = 'attachments/expenses/' . $attachment->attachable_id;
                    break;
            }

            if ($attachmentPath)
            {
                $filePath = storage_path($attachmentPath . '/' . $attachment->filename);

                if (file_exists($filePath))
                {
                    DB::table('attachments')->where('id', $attachment->id)->update(['content' => file_get_contents($filePath)]);

                    try
                    {
                        unlink($filePath);
                    }
                    catch (\Exception $e)
                    {

                    }
                }
            }
        }
    }
}
