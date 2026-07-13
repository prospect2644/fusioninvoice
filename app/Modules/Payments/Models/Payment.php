<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Modules\Payments\Models;

use Carbon\Carbon;
use FI\Modules\Currencies\Models\Currency;
use FI\Modules\CustomFields\Support\CustomFieldsParser;
use FI\Support\CurrencyFormatter;
use FI\Support\DateFormatter;
use FI\Support\NumberFormatter;
use FI\Traits\Sortable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Payment extends Model
{
    use Sortable;

    /**
     * Guarded properties
     * @var array
     */
    protected $guarded = ['id'];

    protected $sortable = ['paid_at', 'invoices.invoice_date', 'invoices.number', 'invoices.summary', 'clients.name', 'amount', 'payment_methods.name', 'note'];

    protected $dates = ['paid_at'];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function custom()
    {
        return $this->hasOne('FI\Modules\CustomFields\Models\PaymentCustom');
    }

    public function client()
    {
        return $this->belongsTo('FI\Modules\Clients\Models\Client');
    }

    public function creditMemo()
    {
        return $this->belongsTo('FI\Modules\Invoices\Models\Invoice', 'credit_memo_id', 'id');
    }

    public function paymentInvoice()
    {
        return $this->hasMany('FI\Modules\Payments\Models\PaymentInvoice');
    }

    public function mailQueue()
    {
        return $this->morphMany('FI\Modules\MailQueue\Models\MailQueue', 'mailable');
    }

    public function notes()
    {
        return $this->morphMany('FI\Modules\Notes\Models\Note', 'notable');
    }

    public function paymentMethod()
    {
        return $this->belongsTo('FI\Modules\PaymentMethods\Models\PaymentMethod');
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

    public function getCurrencyAttribute()
    {
        return Currency::getByCode($this->currency_code);
    }

    public function getFormattedPaidAtAttribute()
    {
        return DateFormatter::format($this->attributes['paid_at']);
    }

    public function getFormattedCreatedAtAttribute()
    {
        return DateFormatter::format($this->attributes['created_at']);
    }

    public function getFormattedNumericAmountAttribute()
    {
        return NumberFormatter::format($this->attributes['amount']);
    }

    public function getFormattedNumericRemainingBalanceAttribute()
    {
        return NumberFormatter::format($this->attributes['remaining_balance']);
    }

    public function getFormattedAmountAttribute()
    {
        return CurrencyFormatter::format($this->attributes['amount'], $this->client->currency);
    }

    public function getFormattedAmountWithCurrencyAttribute()
    {
        if (count($this->paymentInvoice) > 0)
        {
            return CurrencyFormatter::format($this->attributes['amount'], $this->currency);
        }
        else
        {
            return CurrencyFormatter::format($this->attributes['amount'], $this->currency);
        }
    }

    public function getFormattedRemainingBalanceAttribute()
    {
        return CurrencyFormatter::format($this->attributes['remaining_balance'], $this->currency);
    }

    public function getFormattedRemainingBalanceWithCurrencyAttribute()
    {
        if (count($this->paymentInvoice) > 0)
        {
            return CurrencyFormatter::format($this->attributes['remaining_balance'], $this->currency);
        }
        else
        {
            return CurrencyFormatter::format($this->attributes['remaining_balance'], $this->currency);
        }
    }

    public function getFormattedNoteAttribute()
    {
        return nl2br($this->attributes['note']);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeThisYear($query)
    {
        return $query->where(DB::raw('YEAR(paid_at)'), '=', DB::raw('YEAR(CURRENT_DATE())'));
    }

    public function scopeThisMonth($query)
    {
        return $query->where(DB::raw('MONTH(paid_at)'), '=', DB::raw('MONTH(CURRENT_DATE())'))
            ->where(DB::raw('YEAR(paid_at)'), '=', DB::raw('YEAR(CURRENT_DATE())'));
    }

    public function scopeThisQuarter($query)
    {
        return $query->where('paid_at', '>=', Carbon::now()->firstOfQuarter())
            ->where('paid_at', '<=', Carbon::now()->lastOfQuarter());
    }

    public function scopeLastMonth($query)
    {
        return $query->where(DB::raw('paid_at'), '>=', Carbon::now()->subMonths(1)->firstOfMonth())
            ->where(DB::raw('paid_at'), '<=', Carbon::now()->subMonths(1)->lastOfMonth());
    }

    public function scopeLastQuarter($query)
    {
        return $query->where('paid_at', '>=', Carbon::now()->subQuarters(1)->firstOfQuarter())
            ->where('paid_at', '<=', Carbon::now()->subQuarters(1)->lastOfQuarter());
    }

    public function scopeLastYear($query)
    {
        return $query->where('paid_at', '>=', Carbon::now()->subYears(1)->firstOfYear())
            ->where('paid_at', '<=', Carbon::now()->subYears(1)->lastOfYear());
    }

    public function scopeDateRange($query, $fromDate, $toDate)
    {
        return $query->where('paid_at', '>=', $fromDate)
            ->where('paid_at', '<=', $toDate);
    }

    public function scopeKeywords($query, $keywords)
    {
        $keywords = strtolower($keywords);

        if ($keywords)
        {

            $dateFormats     = DateFormatter::formats();
            $mysqlDateFormat = $dateFormats[config('fi.dateFormat')]['mysql'];
            $keywords        = strtolower($keywords);

            $query->where('payments.created_at', 'like', '%' . $keywords . '%')
                ->orWhere('payments.note', 'like', '%' . $keywords . '%')
                ->orWhere(DB::raw("DATE_FORMAT(" . DB::getTablePrefix() . "payments.paid_at,'" . $mysqlDateFormat . "')"), 'like', '%' . $keywords . '%')
                ->orWhereIn('payments.id', function ($query) use ($keywords)
                {
                    $query->select('payment_id')->from('payment_invoices')->leftJoin('invoices', 'payment_invoices.invoice_id', '=', 'invoices.id')
                        ->where(DB::raw('lower(' . DB::getTablePrefix() . 'invoices.number)'), 'like', '%' . $keywords . '%')
                        ->orWhere('invoices.summary', 'like', '%' . $keywords . '%');
                })
                ->orWhereIn('payments.client_id', function ($query) use ($keywords)
                {
                    $query->select('id')->from('clients')->where(DB::raw("CONCAT_WS('^',LOWER(name),LOWER(unique_name))"), 'like', '%' . $keywords . '%');
                })
                ->orWhereIn('payment_method_id', function ($query) use ($keywords)
                {
                    $query->select('id')->from('payment_methods')->where(DB::raw('lower(name)'), 'like', '%' . $keywords . '%');
                });
        }

        return $query;
    }

    public function scopeInvoiceId($query, $invoiceId)
    {
        if ($invoiceId)
        {
            $query->whereHas('invoice', function ($query) use ($invoiceId)
            {
                $query->where('id', $invoiceId);
            });
        }

        return $query;
    }

    public function scopeClientId($query, $clientId)
    {
        if ($clientId)
        {
            $query->whereHas('client', function ($query) use ($clientId)
            {
                $query->where('id', $clientId);
            });
        }

        return $query;
    }

    public function scopeInvoiceNumber($query, $invoiceNumber)
    {
        if ($invoiceNumber)
        {
            $query->whereHas('invoice', function ($query) use ($invoiceNumber)
            {
                $query->where('number', $invoiceNumber);
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
        $customField = config('fi.customFields')->where('tbl_name', 'payments')->where('field_label', $label)->first();

        if ($customField)
        {
            return CustomFieldsParser::getFieldValue($this->custom, $customField, $rawHtml);
        }

        return null;

    }

    public static function prePaymentListForClient($client_id)
    {
        return self::where([
            ['client_id', '=', $client_id],
            ['remaining_balance', '>', 0],
        ])->get();
    }
}
