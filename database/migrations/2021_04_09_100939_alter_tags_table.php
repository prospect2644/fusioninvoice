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

class AlterTagsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        DB::table('tags')
            ->where('tag_entity', 'invoice')
            ->update(['tag_entity' => 'sales']);

        $currentRecurringTags = DB::table('tags')->where('tag_entity', 'recurring_invoice')->get();
        foreach ($currentRecurringTags as $currentRecurringTag)
        {
            $riTagId            = $currentRecurringTag->id;
            $riTagName          = $currentRecurringTag->name;
            $existingSaleEntity = DB::table('tags')->where([
                ['tag_entity', '=', 'sales'],
                ['name', '=', $riTagName],
            ])->first();
            if ($existingSaleEntity)
            {
                $saleId = $existingSaleEntity->id;
                DB::table('tags')->where('id', '=', $riTagId)->delete();
            }
            else
            {
                DB::table('tags')->where('id', $riTagId)->update(['tag_entity' => 'sales']);
                $saleId = $riTagId;
            }

            DB::table('recurring_invoice_tags')->where('tag_id', $riTagId)->update(['tag_id' => $saleId]);
        }

    }
}
