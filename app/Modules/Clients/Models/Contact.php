<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Modules\Clients\Models;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    protected $guarded = ['id'];

    /*
    |--------------------------------------------------------------------------
    | Static Methods
    |--------------------------------------------------------------------------
    */
    public static function getContactTitle()
    {
        return [
            'mr'   => trans('fi.mr'),
            'miss' => trans('fi.miss'),
            'ms'   => trans('fi.ms'),
            'mrs'  => trans('fi.mrs'),
            'dr'   => trans('fi.dr'),
            'prof' => trans('fi.prof'),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function client()
    {
        return $this->belongsTo('FI\Modules\Clients\Models\Client');
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    public function getFormattedContactAttribute()
    {
        return $this->name . ' <' . $this->email . '>';
    }

    public function getFormattedDefaultBccAttribute()
    {
        return ($this->default_bcc) ? trans('fi.yes') : trans('fi.no');
    }

    public function getFormattedDefaultCcAttribute()
    {
        return ($this->default_cc) ? trans('fi.yes') : trans('fi.no');
    }

    public function getFormattedDefaultToAttribute()
    {
        return ($this->default_to) ? trans('fi.yes') : trans('fi.no');
    }

    public function getFormattedNotesAttribute()
    {
        if ($this->notes && strlen($this->notes) > 40)
        {
            return '<span data-toggle="tooltip" title="' . $this->notes . '">' . mb_substr($this->notes, 0, 40) . '</span>';
        }
        elseif ($this->notes && strlen($this->notes) < 40)
        {
            return $this->notes;
        }
        else
        {
            return null;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Other
    |--------------------------------------------------------------------------
    */

    public static function getClientContactList($client_id)
    {
        return Contact::whereClientId($client_id)->pluck('name', 'id')->toArray();
    }

}