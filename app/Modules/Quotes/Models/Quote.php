<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Modules\Quotes\Models;

use Carbon\Carbon;
use FI\Modules\CustomFields\Support\CustomFieldsParser;
use FI\Support\CurrencyFormatter;
use FI\Support\DateFormatter;
use FI\Support\FileNames;
use FI\Support\HTML;
use FI\Support\NumberFormatter;
use FI\Traits\Sortable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use stdClass;

class Quote extends Model
{
    use Sortable;

    protected $guarded = ['id'];

    protected $sortable = [
        'number' => ['LENGTH(number)', 'number'],
        'quote_date',
        'expires_at',
        'clients.name',
        'summary',
        'quote_amounts.total',
        'quote_amounts.tax',
        'quote_amounts.subtotal',
    ];

    protected $dates = ['expires_at', 'quote_date'];

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
        return $this->hasOne('FI\Modules\Quotes\Models\QuoteAmount');
    }

    public function attachments()
    {
        return $this->morphMany('FI\Modules\Attachments\Models\Attachment', 'attachable');
    }

    public function client()
    {
        return $this->belongsTo('FI\Modules\Clients\Models\Client');
    }

    public function clientAttachments()
    {
        $relationship = $this->morphMany('FI\Modules\Attachments\Models\Attachment', 'attachable');

        $relationship->where('client_visibility', 1);

        return $relationship;
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
        return $this->hasOne('FI\Modules\CustomFields\Models\QuoteCustom');
    }

    public function documentNumberScheme()
    {
        return $this->hasOne('FI\Modules\DocumentNumberSchemes\Models\DocumentNumberScheme');
    }

    public function invoice()
    {
        return $this->belongsTo('FI\Modules\Invoices\Models\Invoice');
    }

    public function mailQueue()
    {
        return $this->morphMany('FI\Modules\MailQueue\Models\MailQueue', 'mailable');
    }

    public function items()
    {
        return $this->hasMany('FI\Modules\Quotes\Models\QuoteItem')
            ->orderBy('display_order');
    }

    public function notes()
    {
        return $this->morphMany('FI\Modules\Notes\Models\Note', 'notable');
    }

    // This and items() are the exact same. This is added to appease the IDE gods
    // and the fact that Laravel has a protected items property.
    public function quoteItems()
    {
        return $this->hasMany('FI\Modules\Quotes\Models\QuoteItem')
            ->orderBy('display_order');
    }

    public function transitions()
    {
        return $this->morphMany('FI\Modules\Transitions\Models\Transitions', 'transitionable');
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

    public function getAttachmentPermissionOptionsAttribute()
    {
        return ['0' => trans('fi.not_visible'), '1' => trans('fi.visible')];
    }

    public function getFormattedCreatedAtAttribute()
    {
        return $this->formatted_quote_date;
    }

    public function getFormattedQuoteDateAttribute()
    {
        return DateFormatter::format($this->attributes['quote_date']);
    }

    public function getFormattedUpdatedAtAttribute()
    {
        return DateFormatter::format($this->attributes['updated_at']);
    }

    public function getFormattedExpiresAtAttribute()
    {
        return DateFormatter::format($this->attributes['expires_at']);
    }

    public function getFormattedTermsAttribute()
    {
        return nl2br($this->attributes['terms']);
    }

    public function getFormattedFooterAttribute()
    {
        return nl2br($this->attributes['footer']);
    }

    public function getStatusTextAttribute()
    {
        return $this->attributes['status'];
    }

    public function getPdfFilenameAttribute()
    {
        return FileNames::quote($this);
    }

    public function getPublicUrlAttribute()
    {
        return route('clientCenter.public.quote.show', [$this->url_key]);
    }

    public function getIsForeignCurrencyAttribute()
    {
        if ($this->attributes['currency_code'] == config('fi.baseCurrency'))
        {
            return false;
        }

        return true;
    }

    public function getHtmlAttribute()
    {
        return HTML::quote($this);
    }

    public function getFormattedNumericDiscountAttribute()
    {
        return NumberFormatter::format($this->attributes['discount']);
    }

    /**
     * Gathers a summary of both invoice and item taxes to be displayed on invoice.
     *
     * @return array
     */
    public function getSummarizedTaxesAttribute()
    {
        $taxes = [];

        foreach ($this->items as $item)
        {
            if ($item->taxRate)
            {
                $key = $item->taxRate->name;

                if (!isset($taxes[$key]))
                {
                    $taxes[$key]              = new stdClass();
                    $taxes[$key]->name        = $item->taxRate->name;
                    $taxes[$key]->percent     = $item->taxRate->formatted_percent;
                    $taxes[$key]->total       = $item->amount->tax_1;
                    $taxes[$key]->raw_percent = $item->taxRate->percent;
                }
                else
                {
                    $taxes[$key]->total += $item->amount->tax_1;
                }
            }

            if ($item->taxRate2)
            {
                $key = $item->taxRate2->name;

                if (!isset($taxes[$key]))
                {
                    $taxes[$key]              = new stdClass();
                    $taxes[$key]->name        = $item->taxRate2->name;
                    $taxes[$key]->percent     = $item->taxRate2->formatted_percent;
                    $taxes[$key]->total       = $item->amount->tax_2;
                    $taxes[$key]->raw_percent = $item->taxRate2->percent;
                }
                else
                {
                    $taxes[$key]->total += $item->amount->tax_2;
                }
            }
        }

        foreach ($taxes as $key => $tax)
        {
            $taxes[$key]->total = CurrencyFormatter::format($tax->total, $this->currency);
        }

        return $taxes;
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

    public function scopeClientId($query, $clientId = null)
    {
        if ($clientId)
        {
            $query->where('client_id', $clientId);
        }

        return $query;
    }

    public function scopeCompanyProfileId($query, $companyProfileId)
    {
        if ($companyProfileId)
        {
            $query->where('company_profile_id', $companyProfileId);
        }

        return $query;
    }

    public function scopeDraft($query)
    {
        return $query->where('status', '=', 'draft');
    }

    public function scopeSent($query)
    {
        return $query->where('status', '=', 'sent');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', '=', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', '=', 'rejected');
    }

    public function scopeCanceled($query)
    {
        return $query->where('status', '=', 'canceled');
    }

    public function scopeStatus($query, $status = null)
    {
        switch ($status)
        {
            case 'draft':
                $query->draft();
                break;
            case 'sent':
                $query->sent();
                break;
            case 'viewed':
                $query->viewed();
                break;
            case 'approved':
                $query->approved();
                break;
            case 'rejected':
                $query->rejected();
                break;
            case 'canceled':
                $query->canceled();
                break;
        }

        return $query;
    }

    public function scopeThisYear($query)
    {
        return $query->where(DB::raw('YEAR(quote_date)'), '=', DB::raw('YEAR(CURRENT_DATE())'));
    }

    public function scopeThisMonth($query)
    {
        return $query->where(DB::raw('MONTH(quote_date)'), '=', DB::raw('MONTH(CURRENT_DATE())'))
            ->where(DB::raw('YEAR(quote_date)'), '=', DB::raw('YEAR(CURRENT_DATE())'));
    }

    public function scopeThisQuarter($query)
    {
        return $query->where('quote_date', '>=', Carbon::now()->firstOfQuarter())
            ->where('quote_date', '<=', Carbon::now()->lastOfQuarter());
    }

    public function scopeLastMonth($query)
    {
        return $query->where(DB::raw('quote_date'), '>=', Carbon::now()->subMonths(1)->firstOfMonth())
            ->where(DB::raw('quote_date'), '<=', Carbon::now()->subMonths(1)->lastOfMonth());
    }

    public function scopeLastQuarter($query)
    {
        return $query->where('quote_date', '>=', Carbon::now()->subQuarters(1)->firstOfQuarter())
            ->where('quote_date', '<=', Carbon::now()->subQuarters(1)->lastOfQuarter());
    }

    public function scopeLastYear($query)
    {
        return $query->where('quote_date', '>=', Carbon::now()->subYears(1)->firstOfYear())
            ->where('quote_date', '<=', Carbon::now()->subYears(1)->lastOfYear());
    }

    public function scopeDateRange($query, $fromDate, $toDate)
    {
        return $query->where('quote_date', '>=', $fromDate)
            ->where('quote_date', '<=', $toDate);
    }

    public function scopeKeywords($query, $keywords)
    {
        if ($keywords)
        {
            $keywords = strtolower($keywords);

            $query->where(DB::raw('lower(number)'), 'like', '%' . $keywords . '%')
                ->orWhere('quotes.quote_date', 'like', '%' . $keywords . '%')
                ->orWhere('expires_at', 'like', '%' . $keywords . '%')
                ->orWhere('summary', 'like', '%' . $keywords . '%')
                ->orWhereIn('client_id', function ($query) use ($keywords)
                {
                    $query->select('id')->from('clients')->where(DB::raw("CONCAT_WS('^',LOWER(name),LOWER(unique_name))"), 'like', '%' . $keywords . '%');
                });
        }

        return $query;
    }

    public function scopeCustomField($query, $includeCustomFields = 0)
    {
        if ($includeCustomFields == 1)
        {
            $query->with('custom');
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
        $customField = config('fi.customFields')->where('tbl_name', 'quotes')->where('field_label', $label)->first();

        if ($customField)
        {
            return CustomFieldsParser::getFieldValue($this->custom, $customField, $rawHtml);
        }

        return null;
    }

}