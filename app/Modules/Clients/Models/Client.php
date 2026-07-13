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

use FI\Modules\Currencies\Models\Currency;
use FI\Modules\CustomFields\Support\CustomFieldsParser;
use FI\Modules\Invoices\Models\InvoiceAmount;
use FI\Modules\Payments\Models\Payment;
use FI\Support\CurrencyFormatter;
use FI\Support\DateFormatter;
use FI\Traits\Sortable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;


class Client extends Model
{
    use Sortable;

    protected $guarded = ['id', 'password'];

    protected $hidden = ['password', 'remember_token'];

    protected $sortable = ['id', 'unique_name', 'email', 'phone', 'balance', 'active', 'custom', 'created_at'];

    /*
    |--------------------------------------------------------------------------
    | Static Methods
    |--------------------------------------------------------------------------
    */

    public static function firstOrCreateByUniqueName($uniqueName)
    {
        $client = self::firstOrNew([
            'unique_name' => $uniqueName,
        ]);

        if (!$client->id)
        {
            if (Gate::denies('clients.create'))
            {
                return false;
            }
            $client->name = $uniqueName;
            $client->type = 'customer';
            $client->save();
            return self::find($client->id);
        }

        return $client;
    }

    public static function getStatusList()
    {
        return [
            'active'   => trans('fi.active'),
            'inactive' => trans('fi.inactive'),
        ];
    }

    public static function getTypesList()
    {
        return [
            'lead'      => trans('fi.lead'),
            'prospect'  => trans('fi.prospect'),
            'customer'  => trans('fi.customer'),
            'affiliate' => trans('fi.affiliate'),
        ];
    }

    public static function getClientTitle()
    {
        return [
            'Mr.'   => trans('fi.mr'),
            'Miss'  => trans('fi.miss'),
            'Ms.'   => trans('fi.ms'),
            'Mrs.'  => trans('fi.mrs'),
            'Dr.'   => trans('fi.dr'),
            'Prof.' => trans('fi.prof'),
        ];
    }

    public static function getDropDownList()
    {
        return ['' => trans('fi.select_client')] + self::select('unique_name')->whereActive(1)->orderBy('unique_name')->pluck('unique_name', 'unique_name')->all();
    }

    public static function getParentClients($id = null)
    {
        return self::select('unique_name', 'id')->where('id', '!=', $id)->whereNull('parent_client_id')->orderBy('unique_name')->pluck('unique_name', 'id')->all();
    }

    public static function getChildClients($id)
    {
        return self::select('unique_name', 'id')->where('parent_client_id', $id)->orderBy('unique_name')->pluck('unique_name', 'id')->all();
    }

    public static function getList()
    {
        return self::orderBy('name')->pluck('unique_name', 'unique_name')->all();
    }

    public static function getClientListWithId()
    {
        return ['' => trans('fi.select_client')] + self::select('id', 'name')->whereActive(1)->orderBy('name')->pluck('name', 'id')->all();
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function attachments()
    {
        return $this->morphMany('FI\Modules\Attachments\Models\Attachment', 'attachable');
    }

    public function contacts()
    {
        return $this->hasMany('FI\Modules\Clients\Models\Contact');
    }

    public function currency()
    {
        return $this->belongsTo('FI\Modules\Currencies\Models\Currency', 'currency_code', 'code');
    }

    public function custom()
    {
        return $this->hasOne('FI\Modules\CustomFields\Models\ClientCustom');
    }

    public function expenses()
    {
        return $this->hasMany('FI\Modules\Expenses\Models\Expense');
    }

    public function invoices()
    {
        return $this->hasMany('FI\Modules\Invoices\Models\Invoice');
    }

    public function merchant()
    {
        return $this->hasOne('FI\Modules\Merchant\Models\MerchantClient');
    }

    public function notes()
    {
        return $this->morphMany('FI\Modules\Notes\Models\Note', 'notable');
    }

    public function quotes()
    {
        return $this->hasMany('FI\Modules\Quotes\Models\Quote');
    }

    public function recurringInvoices()
    {
        return $this->hasMany('FI\Modules\RecurringInvoices\Models\RecurringInvoice');
    }

    public function transitions()
    {
        return $this->morphMany('FI\Modules\Transitions\Models\Transitions', 'transitionable');
    }

    public function user()
    {
        return $this->hasOne('FI\Modules\Users\Models\User');
    }

    public function tags()
    {
        return $this->hasMany('FI\Modules\Clients\Models\ClientTag');
    }

    public function tasks()
    {
        return $this->hasMany('FI\Modules\TaskList\Models\Task');
    }

    public function parent()
    {
        return $this->belongsTo('FI\Modules\Clients\Models\Client', 'parent_client_id', 'id');
    }

    public function payments()
    {
        return $this->hasMany('FI\Modules\Payments\Models\Payment');
    }

    public function containers()
    {
        return $this->hasMany('Addons\Containers\Models\Container');
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
        ];
    }

    public function getFormattedBalanceAttribute()
    {
        return CurrencyFormatter::format($this->balance, $this->currency);
    }

    public function getFormattedUpdatedAtAttribute()
    {
        return DateFormatter::format($this->attributes['updated_at']);
    }

    public function getFormattedCreatedAtAttribute()
    {
        return DateFormatter::format($this->attributes['created_at']);
    }

    public function getFormattedPaidAttribute()
    {
        return CurrencyFormatter::format($this->paid, $this->currency);
    }

    public function getFormattedTotalAttribute()
    {
        return CurrencyFormatter::format($this->total, $this->currency);
    }

    public function getFormattedAddressAttribute()
    {
        return nl2br(formatAddress($this));
    }

    public function getLocalTimeAttribute()
    {
        if ($this->timezone)
        {
            return DateFormatter::format(null, true, $this->timezone);

        }

        return trans('fi.unknown');
    }

    public function getClientEmailAttribute()
    {
        return $this->email;
    }

    public function getParentUniqueNameAttribute()
    {
        return ($this->parent) ? $this->parent->unique_name : null;
    }

    public function getShouldEmailPaymentReceiptAttribute()
    {
        if (!$this->email)
        {
            return false;
        }

        switch ($this->automatic_email_payment_receipt)
        {
            case 'yes':
                return true;
            case 'no':
                return false;
            case 'default':
                return config('fi.automaticEmailPaymentReceipts');
            default:
                return false;
        }
    }

    public function getAutomaticEmailOnRecur()
    {
        if (!$this->email)
        {
            return false;
        }

        switch ($this->attributes['automatic_email_on_recur'])
        {
            case 'yes':
                return true;
            case 'no':
                return false;
            case 'default':
                return config('fi.automaticEmailOnRecur');
            default:
                return false;
        }
    }

    public function deleteTags(Client $client)
    {
        $client->tags()->delete();
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeGetSelect()
    {
        return self::select('clients.*',
            DB::raw('(' . $this->getBalanceSql() . ') as balance'),
            DB::raw('(' . $this->getPaidSql() . ') AS paid'),
            DB::raw('(' . $this->getTotalSql() . ') AS total')
        );
    }

    public function scopeStatus($query, $status)
    {
        if ($status == 'active')
        {
            $query->where('active', 1);
        }
        else if ($status == 'inactive')
        {
            $query->where('active', 0);
        }

        return $query;
    }

    public function scopeType($query, $type)
    {
        if ($type)
        {
            $query->where('type', $type);
        }

        return $query;
    }

    public function scopeKeywords($query, $keywords)
    {
        if ($keywords)
        {
            $keywords = explode(' ', $keywords);

            if ($keywords)
            {
                $query->where(function ($query) use ($keywords)
                {
                    foreach ($keywords as $keyword)
                    {
                        // Must match all keywords
                        $query->WhereRaw("CONCAT_WS('^',LOWER(name),LOWER(unique_name),LOWER(email),phone,mobile,address,city,zip) LIKE ?", ['%' . $keyword . '%']);
                    }

                    // Separate the OR portion of the keyword match for ID check. 
                    foreach ($keywords as $keyword)
                    {
                        $query->orWhere('id', $keyword);
                    }
                });
            }
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
    | Subqueries
    |--------------------------------------------------------------------------
    */

    private function getBalanceSql()
    {
        return DB::table('invoice_amounts')->select(DB::raw('sum(balance)'))->whereIn('invoice_id', function ($q)
        {
            $q->select('id')
                ->from('invoices')
                ->where('invoices.client_id', '=', DB::raw(DB::getTablePrefix() . 'clients.id'))
                ->whereRaw(DB::getTablePrefix() . "invoices.status <> 'canceled'");
        })->toSql();
    }

    private function getPaidSql()
    {
        return DB::table('invoice_amounts')->select(DB::raw('sum(paid)'))->whereIn('invoice_id', function ($q)
        {
            $q->select('id')
                ->from('invoices')
                ->where('invoices.client_id', '=', DB::raw(DB::getTablePrefix() . 'clients.id'));
        })->toSql();
    }

    private function getTotalSql()
    {
        return DB::table('invoice_amounts')->select(DB::raw('sum(total)'))->whereIn('invoice_id', function ($q)
        {
            $q->select('id')
                ->from('invoices')
                ->where('invoices.client_id', '=', DB::raw(DB::getTablePrefix() . 'clients.id'));
        })->toSql();
    }

    /*
    |--------------------------------------------------------------------------
    | Other
    |--------------------------------------------------------------------------
    */

    public function customField($label, $rawHtml = true)
    {
        $customField = config('fi.customFields')->where('tbl_name', 'clients')->where('field_label', $label)->first();

        if ($customField)
        {
            return CustomFieldsParser::getFieldValue($this->custom, $customField, $rawHtml);
        }

        return null;

    }

    public function currencyWiseSummary()
    {
        $totalInvoiced          = $this->currencyWiseTotalInvoiced();
        $totalPaidInvoices      = $this->currencyWiseTotalPaidInvoices();
        $totalOpenInvoices      = $this->currencyWiseTotalOpenInvoices();
        $totalOpenCredits       = $this->currencyWiseTotalOpenCredits();
        $totalUnappliedPayments = $this->currencyWiseUnappliedPayments();
        $totalBalance           = $result = [];

        $currencies = array_unique(array_merge(
            array_keys($totalInvoiced),
            array_keys($totalPaidInvoices),
            array_keys($totalOpenInvoices),
            array_keys($totalOpenCredits),
            array_keys($totalUnappliedPayments)
        ));
        $currencies = array_combine($currencies, $currencies);
        foreach ($currencies as $currencyCode)
        {
            $currencyObject = Currency::getByCode($currencyCode);

            $amount                      = $totalOpenInvoices[$currencyCode] ?? 0;
            $openCredit                  = $totalOpenCredits[$currencyCode] ?? 0;
            $unappliedPayments           = $totalUnappliedPayments[$currencyCode] ?? 0;
            $balance                     = ($amount - (abs($openCredit) + $unappliedPayments));
            $totalBalance[$currencyCode] = CurrencyFormatter::format($balance, $currencyObject);


            if (isset($totalInvoiced[$currencyCode]) && !empty($totalInvoiced[$currencyCode]))
            {
                $totalInvoiced[$currencyCode] = CurrencyFormatter::format($totalInvoiced[$currencyCode], $currencyObject);
            }
            if (isset($totalPaidInvoices[$currencyCode]) && !empty($totalPaidInvoices[$currencyCode]))
            {
                $totalPaidInvoices[$currencyCode] = CurrencyFormatter::format($totalPaidInvoices[$currencyCode], $currencyObject);
            }
            if (isset($totalOpenInvoices[$currencyCode]) && !empty($totalOpenInvoices[$currencyCode]))
            {
                $totalOpenInvoices[$currencyCode] = CurrencyFormatter::format($totalOpenInvoices[$currencyCode], $currencyObject);
            }
            if (isset($totalOpenCredits[$currencyCode]) && !empty($totalOpenCredits[$currencyCode]))
            {
                $totalOpenCredits[$currencyCode] = CurrencyFormatter::format($totalOpenCredits[$currencyCode], $currencyObject);
            }
            if (isset($totalUnappliedPayments[$currencyCode]) && !empty($totalUnappliedPayments[$currencyCode]))
            {
                $totalUnappliedPayments[$currencyCode] = CurrencyFormatter::format($totalUnappliedPayments[$currencyCode], $currencyObject);
            }
        }
        foreach ($currencies as $currencyCode)
        {
            $result[$currencyCode]['totalInvoiced']          = $totalInvoiced[$currencyCode] ?? '';
            $result[$currencyCode]['totalPaidInvoices']      = $totalPaidInvoices[$currencyCode] ?? '';
            $result[$currencyCode]['totalOpenInvoices']      = $totalOpenInvoices[$currencyCode] ?? '';
            $result[$currencyCode]['totalOpenCredits']       = $totalOpenCredits[$currencyCode] ?? '';
            $result[$currencyCode]['totalUnappliedPayments'] = $totalUnappliedPayments[$currencyCode] ?? '';
            $result[$currencyCode]['totalBalance']           = $totalBalance[$currencyCode] ?? '';
        }
        return $result;
    }

    public function currencyWiseTotalInvoiced()
    {
        return InvoiceAmount::join('invoices', 'invoice_amounts.invoice_id', '=', 'invoices.id')
            ->where([
                ['invoices.client_id', '=', $this->id],
                ['invoices.status', '!=', 'canceled'],
                ['invoices.type', '=', 'invoice'],
            ])
            ->whereNotNull('invoices.currency_code')
            ->groupBy('currency_code')
            ->selectRaw('sum(total) as total_invoiced, currency_code')
            ->pluck('total_invoiced', 'currency_code')
            ->toArray();
    }

    public function currencyWiseTotalPaidInvoices()
    {
        return InvoiceAmount::join('invoices', 'invoice_amounts.invoice_id', '=', 'invoices.id')
            ->where([
                ['invoices.client_id', '=', $this->id],
                ['invoices.status', '!=', 'canceled'],
                ['invoices.type', '=', 'invoice'],
            ])
            ->whereNotNull('invoices.currency_code')
            ->groupBy('currency_code')
            ->selectRaw('sum(paid) as total_paid, currency_code')
            ->pluck('total_paid', 'currency_code')
            ->toArray();
    }

    public function currencyWiseTotalOpenInvoices()
    {
        return InvoiceAmount::join('invoices', 'invoice_amounts.invoice_id', '=', 'invoices.id')
            ->where([
                ['invoices.client_id', '=', $this->id],
                ['invoices.status', '!=', 'canceled'],
                ['invoices.type', '=', 'invoice'],
            ])
            ->whereNotNull('invoices.currency_code')
            ->groupBy('currency_code')
            ->selectRaw('sum(balance) as total_open_invoices, currency_code')
            ->pluck('total_open_invoices', 'currency_code')
            ->toArray();
    }

    public function currencyWiseTotalOpenCredits()
    {
        return InvoiceAmount::join('invoices', 'invoice_amounts.invoice_id', '=', 'invoices.id')
            ->where([
                ['invoices.client_id', '=', $this->id],
                ['invoices.status', '!=', 'canceled'],
                ['invoices.type', '=', 'credit_memo'],
            ])
            ->whereNotNull('invoices.currency_code')
            ->groupBy('currency_code')
            ->selectRaw('sum(balance) as total_open_credits, currency_code')
            ->pluck('total_open_credits', 'currency_code')
            ->toArray();
    }

    public function currencyWiseUnappliedPayments()
    {
        return Payment::whereClientId($this->id)
            ->whereNotNull('currency_code')
            ->groupBy('currency_code')
            ->selectRaw('sum(remaining_balance) as total_unapplied_payments, currency_code')
            ->pluck('total_unapplied_payments', 'currency_code')
            ->toArray();
    }
}