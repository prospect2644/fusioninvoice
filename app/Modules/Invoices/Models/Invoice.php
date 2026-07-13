<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Modules\Invoices\Models;

use Carbon\Carbon;
use FI\Modules\CustomFields\Support\CustomFieldsParser;
use FI\Modules\Payments\Models\PaymentInvoice;
use FI\Support\CurrencyFormatter;
use FI\Support\DateFormatter;
use FI\Support\FileNames;
use FI\Support\HTML;
use FI\Support\NumberFormatter;
use FI\Traits\Sortable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use stdClass;

class Invoice extends Model
{
    use Sortable;

    protected $guarded = ['id'];

    protected $sortable = [
        'number' => ['LENGTH(number)', 'number'],
        'invoice_date',
        'due_at',
        'clients.name',
        'summary',
        'invoice_amounts.total',
        'invoice_amounts.balance',
        'invoice_amounts.tax',
        'invoice_amounts.subtotal',
    ];

    protected $dates = ['due_at', 'invoice_date'];

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
        return $this->hasOne('FI\Modules\Invoices\Models\InvoiceAmount');
    }

    public function attachments()
    {
        return $this->morphMany('FI\Modules\Attachments\Models\Attachment', 'attachable');
    }

    public function client()
    {
        return $this->belongsTo('FI\Modules\Clients\Models\Client');
    }

    public function tags()
    {
        return $this->hasMany('FI\Modules\Invoices\Models\InvoiceTag');
    }

    public function clientAttachments()
    {
        $relationship = $this->morphMany('FI\Modules\Attachments\Models\Attachment', 'attachable');

        if ($this->status_text == 'paid')
        {
            $relationship->whereIn('client_visibility', [1, 2]);
        }
        else
        {
            $relationship->where('client_visibility', 1);
        }

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
        return $this->hasOne('FI\Modules\CustomFields\Models\InvoiceCustom');
    }

    public function documentNumberScheme()
    {
        return $this->belongsTo('FI\Modules\DocumentNumberSchemes\Models\DocumentNumberScheme');
    }

    public function items()
    {
        return $this->hasMany('FI\Modules\Invoices\Models\InvoiceItem')
            ->orderBy('display_order');
    }

    // This and items() are the exact same. This is added to appease the IDE gods
    // and the fact that Laravel has a protected items property.
    public function invoiceItems()
    {
        return $this->hasMany('FI\Modules\Invoices\Models\InvoiceItem')
            ->orderBy('display_order');
    }

    public function mailQueue()
    {
        return $this->morphMany('FI\Modules\MailQueue\Models\MailQueue', 'mailable');
    }

    public function notes()
    {
        return $this->morphMany('FI\Modules\Notes\Models\Note', 'notable');
    }

    public function payments()
    {
        return $this->hasMany('FI\Modules\Payments\Models\PaymentInvoice');
    }

    public function quote()
    {
        return $this->hasOne('FI\Modules\Quotes\Models\Quote');
    }

    public function transitions()
    {
        return $this->morphMany('FI\Modules\Transitions\Models\Transitions', 'transitionable');
    }

    public function transactions()
    {
        return $this->hasMany('FI\Modules\Merchant\Models\InvoiceTransaction');
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
        return [
            '0' => trans('fi.not_visible'),
            '1' => trans('fi.visible'),
            '2' => trans('fi.visible_after_payment'),
        ];
    }

    public function getFormattedCreatedAtAttribute()
    {
        return $this->formatted_invoice_date;
    }

    public function getFormattedInvoiceDateAttribute()
    {
        return DateFormatter::format($this->attributes['invoice_date']);
    }

    public function getFormattedUpdatedAtAttribute()
    {
        return DateFormatter::format($this->attributes['updated_at']);
    }

    public function getFormattedDueAtAttribute()
    {
        return DateFormatter::format($this->attributes['due_at']);
    }

    public function getDueAt($format)
    {
        return Carbon::parse($this->attributes['due_at'])->format($format);
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

    public function getIsOverdueAttribute()
    {
        if ($this->attributes['due_at'] < date('Y-m-d') and ($this->attributes['status'] == 'sent' or $this->attributes['status'] == 'mailed'))
        {
            return 1;
        }

        return 0;
    }

    public function getPublicUrlAttribute()
    {
        return route('clientCenter.public.invoice.show', [$this->url_key]);
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

    public function getHtmlAttribute()
    {
        return HTML::invoice($this);
    }

    public function getPdfFilenameAttribute()
    {
        return FileNames::invoice($this);
    }

    public function getFormattedNumericDiscountAttribute()
    {
        return NumberFormatter::format($this->attributes['discount']);
    }

    public function getIsPayableAttribute()
    {
        return $this->status_text <> 'canceled' and $this->amount->balance > 0 and $this->type <> 'credit_memo';
    }

    public function getIsAppliableAttribute()
    {
        return (($this->type == 'credit_memo') and (abs($this->amount->balance) > 0) and (!in_array($this->status_text, ['canceled', 'applied'])));
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

    public function scopeDraft($query)
    {
        return $query->where('status', '=', 'draft');
    }

    public function scopeSent($query)
    {
        return $query->where('status', '=', 'sent');
    }

    public function scopeMailed($query)
    {
        return $query->where('status', '=', 'mailed');
    }

    public function scopePaid($query)
    {
        return $query->where('status', '=', 'paid');
    }

    public function scopeCanceled($query)
    {
        return $query->where('status', '=', 'canceled');
    }

    public function scopeCompanyProfileId($query, $companyProfileId)
    {
        if ($companyProfileId)
        {
            $query->where('company_profile_id', $companyProfileId);
        }

        return $query;
    }

    public function scopeNotCanceled($query)
    {
        return $query->where('status', '<>', 'canceled');
    }

    public function scopeStatusIn($query, $statuses)
    {
        return $query->whereIn('status', $statuses);
    }

    public function scopeType($query, $type = 'invoice')
    {
        switch ($type)
        {
            case 'credit_memo':
                $query->where('type', 'credit_memo');
                break;
            default:
                $query->where('type', 'invoice');
                break;
        }

        return $query;
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
            case 'paid':
                $query->paid();
                break;
            case 'canceled':
                $query->canceled();
                break;
            case 'overdue':
                $query->overdue();
                break;
            case 'mailed':
                $query->mailed();
                break;
            case 'unpaid':
                $query->whereHas('amount', function ($q)
                {
                    $q->where('balance', '<>', 0);
                });
                break;
        }

        return $query;
    }

    public function scopeOverdue($query)
    {
        $aOverdueStatusesToCheck = [1 => 'sent', 'mailed'];
        return $query
            ->where('due_at', '<', date('Y-m-d'))
            ->wherein('status', $aOverdueStatusesToCheck);
    }

    public function scopeThisYear($query)
    {
        return $query->where(DB::raw('YEAR(invoice_date)'), '=', DB::raw('YEAR(CURRENT_DATE())'));
    }

    public function scopeThisMonth($query)
    {
        return $query->where(DB::raw('MONTH(invoice_date)'), '=', DB::raw('MONTH(CURRENT_DATE())'))
            ->where(DB::raw('YEAR(invoice_date)'), '=', DB::raw('YEAR(CURRENT_DATE())'));
    }

    public function scopeThisQuarter($query)
    {
        return $query->where('invoice_date', '>=', Carbon::now()->firstOfQuarter())
            ->where('invoice_date', '<=', Carbon::now()->lastOfQuarter());
    }

    public function scopeLastMonth($query)
    {
        return $query->where(DB::raw('invoice_date'), '>=', Carbon::now()->subMonths(1)->firstOfMonth())
            ->where(DB::raw('invoice_date'), '<=', Carbon::now()->subMonths(1)->lastOfMonth());
    }

    public function scopeLastQuarter($query)
    {
        return $query->where('invoice_date', '>=', Carbon::now()->subQuarters(1)->firstOfQuarter())
            ->where('invoice_date', '<=', Carbon::now()->subQuarters(1)->lastOfQuarter());
    }

    public function scopeLastYear($query)
    {
        return $query->where('invoice_date', '>=', Carbon::now()->subYears(1)->firstOfYear())
            ->where('invoice_date', '<=', Carbon::now()->subYears(1)->lastOfYear());
    }

    public function scopeDateRange($query, $fromDate, $toDate)
    {
        return $query->where('invoice_date', '>=', $fromDate)
            ->where('invoice_date', '<=', $toDate);
    }

    public function scopeThisYearOverdue($query)
    {
        return $query->where(DB::raw('YEAR(due_at)'), '=', DB::raw('YEAR(CURRENT_DATE())'));
    }

    public function scopeThisMonthOverdue($query)
    {
        return $query->where(DB::raw('MONTH(due_at)'), '=', DB::raw('MONTH(CURRENT_DATE())'))
            ->where(DB::raw('YEAR(due_at)'), '=', DB::raw('YEAR(CURRENT_DATE())'));
    }

    public function scopeThisQuarterOverdue($query)
    {
        return $query->where('due_at', '>=', Carbon::now()->firstOfQuarter())
            ->where('due_at', '<=', Carbon::now()->lastOfQuarter());
    }

    public function scopeLastMonthOverdue($query)
    {
        // return $query->where(DB::raw('MONTH(due_at)'), '=', DB::raw('MONTH(CURRENT_DATE() - INTERVAL 1 MONTH)'))
        //     ->where(DB::raw('YEAR(due_at)'), '=', DB::raw('YEAR(CURRENT_DATE() - INTERVAL 1 MONTH)'));
        
        return $query->where(DB::raw('due_at'), '>=', Carbon::now()->subMonths(1)->firstOfMonth())
            ->where(DB::raw('due_at'), '<=', Carbon::now()->subMonths(1)->lastOfMonth());

    }

    public function scopeLastQuarterOverdue($query)
    {
        return $query->where('due_at', '>=', Carbon::now()->subQuarters(1)->firstOfQuarter())
            ->where('due_at', '<=', Carbon::now()->subQuarters(1)->lastOfQuarter());
    }

    public function scopeLastYearOverdue($query)
    {
        return $query->where('due_at', '>=', Carbon::now()->subYears(1)->firstOfYear())
            ->where('due_at', '<=', Carbon::now()->subYears(1)->lastOfYear());
    }

    public function scopeDateRangeOverdue($query, $fromDate, $toDate)
    {
        return $query->where('due_at', '>=', $fromDate)
            ->where('due_at', '<=', $toDate);
    }

    public function scopeKeywords($query, $keywords = null)
    {
        if ($keywords)
        {
            $keywords = strtolower($keywords);

            $query->where(DB::raw('lower(number)'), 'like', '%' . $keywords . '%')
                ->orWhere('invoices.invoice_date', 'like', '%' . $keywords . '%')
                ->orWhere('due_at', 'like', '%' . $keywords . '%')
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
        $customField = config('fi.customFields')->where('tbl_name', 'invoices')->where('field_label', $label)->first();

        if ($customField)
        {
            return CustomFieldsParser::getFieldValue($this->custom, $customField, $rawHtml);
        }

        return null;

    }

    public static function creditMemoListForClient($client_id)
    {
        return self::where([
            ['type', '=', 'credit_memo'],
            ['status', '!=', 'applied'],
            ['client_id', '=', $client_id],
        ])->get();
    }

    public static function invoiceListForClient($client_id)
    {
        return self::where([
            ['type', '=', 'invoice'],
            ['client_id', '=', $client_id],
        ])->whereIn('status', ['draft', 'sent'])->get();
    }

    public function getCreditApplication()
    {
        $result = PaymentInvoice::query()
            ->with(['payment', 'invoice'])
            ->whereHas('payment', function ($q)
            {
                $q->where('credit_memo_id', '=', $this->id);
            })
            ->get();

        return $result;
    }

    public function getShortSummaryAttribute()
    {
        return (mb_strlen($this->summary) > 50) ? mb_substr($this->summary, 0, 50) . '...' : $this->summary;
    }

    public function deleteTags(Invoice $invoice)
    {
        $invoice->tags()->delete();
    }

}
