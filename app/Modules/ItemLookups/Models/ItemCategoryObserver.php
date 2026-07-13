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

class ItemCategoryObserver
{
    public function deleted(ItemCategory $itemCategory)
    {
        ItemLookup::where('category_id', $itemCategory->id)->update(['category_id' => 0]);
    }
}