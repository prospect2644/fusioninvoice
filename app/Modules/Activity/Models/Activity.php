<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Modules\Activity\Models;

use FI\Support\DateFormatter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;

class Activity extends Model
{
    protected $table = 'activities';

    protected $guarded = ['id'];

    public function audit()
    {
        return $this->morphTo();
    }

    public function getFormattedActivityAttribute()
    {

        if ($this->audit)
        {
            switch ($this->audit_type)
            {
                case 'FI\Modules\Quotes\Models\Quote':

                    switch ($this->activity)
                    {
                        case 'public.viewed':
                            return trans('fi.activity_quote_viewed', ['number' => $this->audit->number, 'link' => Gate::allows('quotes.update') ? route('quotes.edit', [$this->audit->id]) : '#' ]);
                            break;

                        case 'public.approved':
                            return trans('fi.activity_quote_approved', ['number' => $this->audit->number, 'link' => Gate::allows('quotes.update') ? route('quotes.edit', [$this->audit->id]) : '#' ]);
                            break;

                        case 'public.rejected':
                            return trans('fi.activity_quote_rejected', ['number' => $this->audit->number, 'link' => Gate::allows('quotes.update') ? route('quotes.edit', [$this->audit->id]) : '#']);
                            break;
                    }

                    break;

                case 'FI\Modules\Invoices\Models\Invoice':

                    switch ($this->activity)
                    {
                        case 'public.viewed':
                            return trans('fi.activity_invoice_viewed', ['number' => $this->audit->number, 'link' => Gate::allows('invoices.update') ? route('invoices.edit', [$this->audit->id]) : '#' ]);
                            break;
                        case 'public.paid':
                            return trans('fi.activity_invoice_paid', ['number' => $this->audit->number, 'link' => Gate::allows('invoices.update') ? route('invoices.edit', [$this->audit->id]) : '#' ]);
                            break;
                    }

                    break;
            }
        }

        return '';
    }

    public function getFormattedCreatedAtAttribute()
    {
        return DateFormatter::format($this->created_at, true);
    }
}