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
use FI\Support\CurrencyFormatter;
use FI\Support\DateFormatter;
use FI\Support\FileNames;
use FI\Support\HTML;
use FI\Traits\Sortable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PaymentInvoice extends Model
{
    use Sortable;

    /**
     * Guarded properties
     * @var array
     */
    protected $guarded = ['id'];

    protected $sortable = ['created_at', 'invoices.invoice_date', 'invoices.number', 'invoices.summary'];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function custom()
    {
        return $this->hasMany('FI\Modules\Payments\Models\Payments');
    }

    public function invoice()
    {
        return $this->belongsTo('FI\Modules\Invoices\Models\Invoice');
    }

    public function payment()
    {
        return $this->belongsTo('FI\Modules\Payments\Models\Payment');
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */
    public function getFormattedInvoiceAmountPaidAttribute()
    {
        return CurrencyFormatter::format($this->attributes['invoice_amount_paid'], $this->invoice->currency);
    }

    public function getFormattedPaidAtAttribute()
    {
        return DateFormatter::format($this->attributes['created_at']);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeThisYear($query)
    {
        return $query->where(DB::raw('YEAR(' . DB::getTablePrefix() . 'payment_invoices.created_at)'), '=', DB::raw('YEAR(CURRENT_DATE())'));
    }

    public function scopeThisMonth($query)
    {
        return $query->where(DB::raw('MONTH(' . DB::getTablePrefix() . 'payment_invoices.created_at)'), '=', DB::raw('MONTH(CURRENT_DATE())'))
            ->where(DB::raw('YEAR(' . DB::getTablePrefix() . 'payment_invoices.created_at)'), '=', DB::raw('YEAR(CURRENT_DATE())'));
    }

    // public function scopeThisQuarter($query)
    // {
    //     return $query->where('payment_invoices.created_at', '>=', Carbon::now()->subMonth(3)->firstOfQuarter())
    //         ->where('payment_invoices.created_at', '<=', Carbon::now()->subMonth(3)->lastOfQuarter());
    // }

    // public function scopeLastMonth($query)
    // {
    //     return $query->where(DB::raw('MONTH('.DB::getTablePrefix().'payment_invoices.created_at)'), '=', DB::raw('MONTH(DATE_SUB(CURRENT_DATE(), INTERVAL 1 MONTH))'))
    //         ->where(DB::raw('YEAR('.DB::getTablePrefix().'payment_invoices.created_at)'), '=', DB::raw('YEAR(CURRENT_DATE())'));
    // }

    // -mw-
    public function scopeThisQuarter($query)
    {
        return $query->where('payment_invoices.created_at', '>=', Carbon::now()->firstOfQuarter())
            ->where('payment_invoices.created_at', '<=', Carbon::now()->lastOfQuarter());
    }

    public function scopeLastMonth($query)
    {
        return $query->where(DB::raw('payment_invoices.created_at'), '>=', Carbon::now()->subMonths(1)->firstOfMonth())
            ->where(DB::raw('payment_invoices.created_at'), '<=', Carbon::now()->subMonths(1)->lastOfMonth());
    }
    // -mw-

    public function scopeLastQuarter($query)
    {
        return $query->where('payment_invoices.created_at', '>=', Carbon::now()->subQuarters(1)->firstOfQuarter())
            ->where('payment_invoices.created_at', '<=', Carbon::now()->subQuarters(1)->lastOfQuarter());
    }

    public function scopeLastYear($query)
    {
        return $query->where(DB::getTablePrefix() . 'payment_invoices.created_at', '>=', Carbon::now()->subYears(1)->firstOfYear())
            ->where(DB::getTablePrefix() . 'payment_invoices.created_at', '<=', Carbon::now()->subYears(1)->lastOfYear());
    }

    public function scopeDateRange($query, $from, $to)
    {
        return $query->where('payment_invoices.created_at', '>=', Carbon::parse($from)->format('Y-m-d'))->where('payment_invoices.created_at', '<=', Carbon::parse($to)->format('Y-m-d'));
    }

    public function scopeYear($query, $year)
    {
        return $query->where(DB::raw('YEAR(' . DB::getTablePrefix() . 'payment_invoices.created_at)'), '=', $year);
    }

    public function scopeClientId($query, $clientId)
    {
        if ($clientId)
        {
            $query->whereHas('invoice', function ($query) use ($clientId)
            {
                $query->where('client_id', $clientId);
            });
        }

        return $query;
    }

    public function getFormattedAmountAttribute()
    {
        return CurrencyFormatter::format($this->attributes['amount'], $this->invoice->currency);
    }

    public function getUserAttribute()
    {
        return $this->invoice->user;
    }

    public function getHtmlAttribute()
    {
        return HTML::invoice($this->invoice);
    }

    public function getPdfFilenameAttribute()
    {
        return FileNames::invoice($this->invoice);
    }
}