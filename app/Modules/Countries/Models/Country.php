<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Modules\Countries\Models;

use FI\Traits\Sortable;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    use Sortable;

    protected $table = 'countries';

    protected $sortable = ['code', 'name'];

    /**
     * Guarded properties
     * @var array
     */
    protected $guarded = ['id'];

    public static function getAll()
    {
        return self::orderBy('name')->pluck('name', 'name')->all();
    }
}