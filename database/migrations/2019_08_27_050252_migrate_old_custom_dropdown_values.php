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

class MigrateOldCustomDropdownValues extends Migration
{
    public function up()
    {
        $dropDownCustomFields = DB::table('custom_fields')->where('field_type', 'dropdown')->get();
        if ($dropDownCustomFields)
        {
            foreach ($dropDownCustomFields as $field)
            {
                $jsonFieldMeta = [
                    'default' => '',
                    'options' => explode(',', $field->field_meta),
                ];
                DB::table('custom_fields')->where('id', $field->id)->update(['field_meta' => json_encode($jsonFieldMeta, JSON_PRETTY_PRINT)]);
            }
        }
    }
}