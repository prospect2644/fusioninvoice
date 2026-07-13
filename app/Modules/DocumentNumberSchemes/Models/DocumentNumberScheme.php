<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Modules\DocumentNumberSchemes\Models;

use FI\Traits\Sortable;
use Illuminate\Database\Eloquent\Model;

class DocumentNumberScheme extends Model
{
    use Sortable;

    protected $guarded = ['id'];

    protected $sortable = ['name', 'format', 'next_id', 'left_pad', 'reset_number','type'];

    /*
    |--------------------------------------------------------------------------
    | Static Methods
    |--------------------------------------------------------------------------
    */

    public static function findIdByName($name)
    {
        if ($documentNumberScheme = self::where('name', $name)->first())
        {
            return $documentNumberScheme->id;
        }

        return null;
    }

    public static function generateNumber($id, $invoice_prefix)
    {
        $documentNumberScheme = self::find($id);

        // Only check for resets if this group has been used.
        if ($documentNumberScheme->last_id <> 0)
        {
            // Check for yearly reset.
            if ($documentNumberScheme->reset_number == 1)
            {
                if ($documentNumberScheme->last_year <> date('Y'))
                {
                    $documentNumberScheme->next_id = 1;
                    $documentNumberScheme->save();
                }
            }
            // Check for monthly reset.
            elseif ($documentNumberScheme->reset_number == 2)
            {
                if ($documentNumberScheme->last_month <> date('m') or $documentNumberScheme->last_year <> date('Y'))
                {
                    $documentNumberScheme->next_id = 1;
                    $documentNumberScheme->save();
                }
            }
            // Check for weekly reset.
            elseif ($documentNumberScheme->reset_number == 3)
            {
                if ($documentNumberScheme->last_week <> date('W') or $documentNumberScheme->last_month <> date('m') or $documentNumberScheme->last_year <> date('Y'))
                {
                    $documentNumberScheme->next_id = 1;
                    $documentNumberScheme->save();
                }
            }
        }

        $number = $documentNumberScheme->format;

        $number = str_replace('{NUMBER}', str_pad($documentNumberScheme->next_id, $documentNumberScheme->left_pad, '0', STR_PAD_LEFT), $number);
        $number = str_replace('{YEAR}', date('Y'), $number);
        $number = str_replace('{MONTH}', date('m'), $number);
        $number = str_replace('{WEEK}', date('W'), $number);
        $number = str_replace('{MONTHSHORTNAME}', date('M'), $number);
        $number = str_replace('{INVOICE_PREFIX}', $invoice_prefix, $number);

        $documentNumberScheme->last_id    = $documentNumberScheme->next_id;
        $documentNumberScheme->last_week  = date('W');
        $documentNumberScheme->last_month = date('m');
        $documentNumberScheme->last_year  = date('Y');
        $documentNumberScheme->save();

        return $number;
    }

    public static function getList()
    {
        return self::orderBy('name')->pluck('name', 'id')->all();
    }
    public static function getListGroup()
    {
        $documentNumberSchemes = [];
        $results = self::orderBy('type')->orderBy('name')->get();
        foreach ($results as $result){
            $documentNumberSchemes[$result->type][$result->id] = $result->name;
        }
        return $documentNumberSchemes;
    }

    public static function incrementNextId($document)
    {
        $documentNumberScheme              = self::find($document->document_number_scheme_id);
        $documentNumberScheme->next_id     = $documentNumberScheme->next_id + 1;
        $documentNumberScheme->last_number = $document->number;
        $documentNumberScheme->save();
    }
}