<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Modules\Tags\Models;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{

    protected $guarded = ['id'];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function clientTags()
    {
        return $this->hasMany('FI\Modules\Clients\Models\ClientTag', 'tag_id');
    }

    public function invoiceTags()
    {
        return $this->hasMany('FI\Modules\Clients\Models\InvoiceTag', 'tag_id');
    }

    public function noteTags()
    {
        return $this->hasMany('FI\Modules\Notes\Models\NoteTag', 'tag_id');
    }

    public function recurringInvoiceTags()
    {
        return $this->hasMany('FI\Modules\RecurringInvoices\Models\RecurringInvoiceTag', 'tag_id');
    }
}