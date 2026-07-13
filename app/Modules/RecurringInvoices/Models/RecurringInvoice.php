<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Modules\RecurringInvoices\Models;

use FI\Modules\CustomFields\Support\CustomFieldsParser;
use FI\Support\DateFormatter;
use FI\Support\NumberFormatter;
use FI\Traits\Sortable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class RecurringInvoice extends Model
{
    use Sortable;

    protected $guarded = ['id'];

    protected $sortable = ['id', 'clients.name', 'summary', 'next_date', 'stop_date', 'recurring_invoice_amounts.total'];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function activities()
    {
        return $this->morphMany('FI\Modules\Activity\Models\Activity', 'audit');
    }

    public function amount()
    {
        return $this->hasOne('FI\Modules\RecurringInvoices\Models\RecurringInvoiceAmount');
    }

    public function client()
    {
        return $this->belongsTo('FI\Modules\Clients\Models\Client');
    }

    public function tags()
    {
        return $this->hasMany('FI\Modules\RecurringInvoices\Models\RecurringInvoiceTag');
    }

    public function companyProfile()
    {
        return $this->belongsTo('FI\Modules\CompanyProfiles\Models\CompanyProfile');
    }

    public function currency()
    {
        return $this->belongsTo('FI\Modules\Currencies\Models\Currency', 'currency_code', 'code');
    }

    public function custom()
    {
        return $this->hasOne('FI\Modules\CustomFields\Models\RecurringInvoiceCustom');
    }

    public function documentNumberScheme()
    {
        return $this->belongsTo('FI\Modules\DocumentNumberSchemes\Models\DocumentNumberScheme');
    }

    public function items()
    {
        return $this->hasMany('FI\Modules\RecurringInvoices\Models\RecurringInvoiceItem')
            ->orderBy('display_order');
    }

    // This and items() are the exact same. This is added to appease the IDE gods
    // and the fact that Laravel has a protected items property.
    public function recurringInvoiceItems()
    {
        return $this->hasMany('FI\Modules\RecurringInvoices\Models\RecurringInvoiceItem')
            ->orderBy('display_order');
    }

    public function user()
    {
        return $this->belongsTo('FI\Modules\Users\Models\User');
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    public function getFormattedFooterAttribute()
    {
        return nl2br($this->attributes['footer']);
    }

    public function getFormattedNextDateAttribute()
    {
        if ($this->attributes['next_date'] <> '0000-00-00')
        {
            return DateFormatter::format($this->attributes['next_date']);
        }

        return '';
    }

    public function getFormattedNumericDiscountAttribute()
    {
        return NumberFormatter::format($this->attributes['discount']);
    }

    public function getFormattedStopDateAttribute()
    {
        if ($this->attributes['stop_date'] <> '0000-00-00')
        {
            return DateFormatter::format($this->attributes['stop_date']);
        }

        return '';
    }

    public function getFormattedTermsAttribute()
    {
        return nl2br($this->attributes['terms']);
    }

    public function getIsForeignCurrencyAttribute()
    {
        if ($this->attributes['currency_code'] == config('fi.baseCurrency'))
        {
            return false;
        }

        return true;
    }

    public function getFormattedTagsAttribute()
    {
        $tags        = $this->tags;
        $invoiceTags = [];
        foreach ($tags as $tag)
        {
            $invoiceTags[] = $tag->tag->name;
        }
        if (empty($invoiceTags))
        {
            return '';
        }
        else
        {
            if (count($invoiceTags) == 1)
            {
                return $invoiceTags[0];
            }
            else if (count($invoiceTags) == 2)
            {
                return $invoiceTags[0] . ', ' . $invoiceTags[1];
            }
            else
            {
                return $invoiceTags[0] . ', ' . $invoiceTags[1] . '..';
            }

        }

    }

    public function getShortSummaryAttribute()
    {
        return (mb_strlen($this->summary) > 50) ? mb_substr($this->summary, 0, 50) . '...' : $this->summary;
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeActive($query)
    {
        return $query->where('stop_date', '0000-00-00')
            ->orWhere('stop_date', '>', date('Y-m-d'));
    }

    public function scopeClientId($query, $clientId = null)
    {
        if ($clientId)
        {
            $query->where('client_id', $clientId);
        }

        return $query;
    }

    public function scopeCompanyProfileId($query, $companyProfileId = null)
    {
        if ($companyProfileId)
        {
            $query->where('company_profile_id', $companyProfileId);
        }

        return $query;
    }

    public function scopeInactive($query)
    {
        return $query->where('stop_date', '<>', '0000-00-00')
            ->where('stop_date', '<=', date('Y-m-d'));
    }

    public function scopeKeywords($query, $keywords = null)
    {
        if ($keywords)
        {
            $keywords = strtolower($keywords);

            $query->where('summary', 'like', '%' . $keywords . '%')
                ->orWhereIn('client_id', function ($query) use ($keywords)
                {
                    $query->select('id')->from('clients')->where(DB::raw("CONCAT_WS('^',LOWER(name),LOWER(unique_name))"), 'like', '%' . $keywords . '%');
                });
        }

        return $query;
    }

    public function scopeRecurNow($query)
    {
        $query->where('next_date', '<>', '0000-00-00');
        $query->where('next_date', '<=', date('Y-m-d'));
        $query->where(function ($q)
        {
            $q->where('stop_date', '0000-00-00');
            $q->orWhere('next_date', '<=', DB::raw('stop_date'));
        });

        return $query;
    }

    public function scopeStatus($query, $status = null)
    {
        switch ($status)
        {
            case 'active':
                return $query->active();
            case 'inactive':
                return $query->inactive();
        }

        return $query;
    }

    public function scopeByDateRange($query, $from, $to)
    {
        return $query->where(function ($q) use ($from, $to)
        {
            $q->where('next_date', '>=', $from)->where('next_date', '<=', $to);
        });
    }

    public function scopeTags($query, $tags, $tagsMustMatchAll)
    {
        if (!empty($tags))
        {
            if ($tagsMustMatchAll)
            {
                $query->whereHas('tags', function ($query) use ($tags)
                {
                    $query->whereIn("tag_id", $tags);

                }, "=", count($tags));

            }
            else
            {
                $query->whereHas('tags', function ($query) use ($tags)
                {
                    $query->whereIn("tag_id", $tags);

                });
            }
        }

        return $query;
    }

    /*
    |--------------------------------------------------------------------------
    | Other
    |--------------------------------------------------------------------------
    */

    public function customField($label, $rawHtml = true)
    {
        $customField = config('fi.customFields')->where('tbl_name', 'recurring_invoices')->where('field_label', $label)->first();

        if ($customField)
        {
            return CustomFieldsParser::getFieldValue($this->custom, $customField, $rawHtml);
        }

        return null;

    }

    public function deleteTags(RecurringInvoice $invoice)
    {
        $invoice->tags()->delete();
    }
}