<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Modules\ItemLookups\Models;

class ItemLookupObserver
{

    public function creating(ItemLookup $itemLookup)
    {
        if (request('category_name'))
        {
            $itemLookup->category_id = ItemCategory::firstOrCreate(['name' => request('category_name')])->id;
        }
    }

    public function updating(ItemLookup $itemLookup)
    {
        if (request('category_name'))
        {
            $itemLookup->category_id = ItemCategory::firstOrCreate(['name' => request('category_name')])->id;
        }
    }
}