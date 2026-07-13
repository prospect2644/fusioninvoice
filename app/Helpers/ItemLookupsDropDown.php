<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use FI\Modules\ItemLookups\Models\ItemLookup;

function itemLookUpsDropDown($default = '', $class = '')
{
    $items       = ItemLookup::all();
    $defaultItem = $default != '' ? ItemLookup::whereName($default->name)->first() : null;
    $dropDown    = '<select class="form-control input-sm ' . $class . ' " name="name">';
    $dropDown .= '<option value="">' . trans('fi.select-item') . '</option>';
    $defaultItemId = false;
    foreach ($items as $item)
    {

        if ($defaultItem != '' && $item->id == $defaultItem->id)
        {
            $dropDown .= '<option value="' . $item->id . '" selected>' . $item->name . '</option>';
            $defaultItemId = true;
        }
        else
        {
            $dropDown .= '<option value="' . $item->id . '">' . $item->name . '</option>';
        }
    }

    if ($default != '' && $defaultItemId == false)
    {
        $dropDown .= '<option value="' . $default->name . '" selected>' . $default->name . '</option>';
    }
    $dropDown .= '</select>';

    return $dropDown;
}