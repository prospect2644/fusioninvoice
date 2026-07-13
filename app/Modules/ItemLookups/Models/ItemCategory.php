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

use Illuminate\Database\Eloquent\Model;
use FI\Traits\Sortable;

class ItemCategory extends Model
{
    use Sortable;

    protected $table = 'item_categories';

    protected $guarded = ['id'];

    protected $sortable = ['name'];

    /*
    |--------------------------------------------------------------------------
    | Static Methods
    |--------------------------------------------------------------------------
    */

    public static function getList()
    {
        return self::whereIn('id', function ($query)
        {
            $query->select('category_id')->distinct()->from('item_lookups');
        })->orderBy('name')->pluck('name', 'id')->all();
    }

    public static function getDropDownList()
    {
        return ['' => trans('fi.select-item-category')] + self::select('name')->orderBy('name')->pluck('name', 'name')->all();
    }
}