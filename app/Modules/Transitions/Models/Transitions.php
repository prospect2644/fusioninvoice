<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Modules\Transitions\Models;

use Carbon\Carbon;
use DB;
use FI\Support\DateFormatter;
use Illuminate\Database\Eloquent\Model;

class Transitions extends Model
{
    protected $guarded = ['id'];

    public function transitionable()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo('FI\Modules\Users\Models\User')->withTrashed();
    }

    public function client()
    {
        return $this->belongsTo('FI\Modules\Clients\Models\Client');
    }

    public function note()
    {
        return $this->belongsTo('FI\Modules\Notes\Models\Note', 'transitionable_id')->where('transitions.transitionable_type', 'FI\Modules\Notes\Models\Note');
    }

    public function transitionClient()
    {
        return $this->belongsTo('FI\Modules\Clients\Models\Client', 'transitionable_id')->where('transitions.transitionable_type', 'FI\Modules\Clients\Models\Client');
    }

    public function invoice()
    {
        return $this->belongsTo('FI\Modules\Invoices\Models\Invoice', 'transitionable_id')
            ->where([
                ['transitions.transitionable_type', '=', 'FI\Modules\Invoices\Models\Invoice'],
                ['type', '=', 'invoice'],
            ]);
    }

    public function creditMemo()
    {
        return $this->belongsTo('FI\Modules\Invoices\Models\Invoice', 'transitionable_id')
            ->where([
                ['transitions.transitionable_type', '=', 'FI\Modules\Invoices\Models\Invoice'],
                ['type', '=', 'credit_memo'],
            ]);
    }

    public function quote()
    {
        return $this->belongsTo('FI\Modules\Quotes\Models\Quote', 'transitionable_id')->where('transitions.transitionable_type', 'FI\Modules\Quotes\Models\Quote');
    }

    public function recurringInvoice()
    {
        return $this->belongsTo('FI\Modules\RecurringInvoices\Models\RecurringInvoice', 'transitionable_id')->where('transitions.transitionable_type', 'FI\Modules\RecurringInvoices\Models\RecurringInvoice');
    }

    public function payment()
    {
        return $this->belongsTo('FI\Modules\Payments\Models\Payment', 'transitionable_id')->where('transitions.transitionable_type', 'FI\Modules\Payments\Models\Payment');
    }

    public function paymentInvoice()
    {
        return $this->belongsTo('FI\Modules\Payments\Models\PaymentInvoice', 'transitionable_id')->where('transitions.transitionable_type', 'FI\Modules\Payments\Models\PaymentInvoice');
    }

    public function task()
    {
        return $this->belongsTo('FI\Modules\TaskList\Models\Task', 'transitionable_id')->where('transitions.transitionable_type', 'FI\Modules\TaskList\Models\Task');
    }

    public function expense()
    {
        return $this->belongsTo('FI\Modules\Expenses\Models\Expense', 'transitionable_id')->where('transitions.transitionable_type', 'FI\Modules\Expenses\Models\Expense');
    }

    public function attachment()
    {
        return $this->belongsTo('FI\Modules\Attachments\Models\Attachment', 'transitionable_id')->where('transitions.transitionable_type', 'FI\Modules\Attachments\Models\Attachment');
    }

    public function getFormattedCreatedAtAttribute()
    {
        if (Carbon::parse($this->attributes['created_at'])->diffInDays(Carbon::now()) <= 7)
        {
            Carbon::setLocale(app()->getLocale());
            return Carbon::parse($this->attributes['created_at'])->diffForHumans();
        }
        else
        {
            return DateFormatter::format($this->attributes['created_at'], true);
        }
    }

    public function getFormattedCreatedAtSystemFormatAttribute()
    {
        return DateFormatter::format($this->attributes['created_at'], true);
    }

    public function getFormattedCreatedAtNewlineAttribute()
    {
        return DateFormatter::format($this->attributes['created_at'], false) . '<br>' . DateFormatter::extractTime($this->attributes['created_at']);
    }

    public function getTransitionEntityAttribute()
    {
        $description = $info = '';
        $detail      = json_decode($this->detail);

        if ($this->transitionable_type == 'FI\Modules\Clients\Models\Client')
        {
            if ($this->action_type == 'client_created')
            {
                $description = trans('fi.transition.client.client_created', ['client_type' => trans('fi.' . $detail->type)]);
                $info        = (isset($detail->name)) ? trans('fi.client_name') . ': ' . $detail->name : '';
            }
            elseif ($this->action_type == 'type_changed')
            {
                $description = trans('fi.transition.client.type_changed', ['previous_value' => trans('fi.' . $this->previous_value), 'current_value' => trans('fi.' . $this->current_value)]);
                $info        = (isset($detail->name)) ? trans('fi.client_name') . ': ' . $detail->name : '';
            }
            elseif ($this->action_type == 'updated')
            {
                $description = trans('fi.transition.client.updated');
                $info        = (isset($detail->name)) ? trans('fi.client_name') . ': ' . $detail->name : '';
            }
            elseif ($this->action_type == 'deleted')
            {
                $description = trans('fi.transition.client.deleted');
                $info        = (isset($detail->name)) ? trans('fi.client_name') . ': ' . $detail->name : '';
            }
            elseif ($this->action_type == 'status_changed')
            {
                $description = trans('fi.transition.client.status_changed', ['current_value' => $this->current_value == 1 ? trans('fi.active') : trans('fi.inactive')]);
                $info        = (isset($detail->name)) ? trans('fi.client_name') . ': ' . $detail->name : '';
            }
        }

        if ($this->transitionable_type == 'FI\Modules\Invoices\Models\Invoice')
        {
            if ($this->action_type == 'created')
            {
                $description = trans('fi.transition.invoice.created', ['invoice_number' => '#' . $detail->number]);
                $info        = (isset($detail->amount)) ? trans('fi.total') . ': ' . $detail->amount : '';
            }
            if ($this->action_type == 'credit_memo_created')
            {
                $description = trans('fi.transition.invoice.credit_memo_created', ['credit_memo_number' => '#' . $detail->number]);
                $info        = (isset($detail->amount)) ? trans('fi.total') . ': ' . $detail->amount : '';
            }
            if ($this->action_type == 'created_from_recurring')
            {
                $description = trans('fi.transition.invoice.created_from_recurring', ['invoice_number' => '#' . $detail->number, 'recurring_invoice_id' => '#' . $detail->recurring_invoice_id]);
                $info        = (isset($detail->amount)) ? trans('fi.total') . ': ' . $detail->amount : '';
            }
            elseif ($this->action_type == 'updated')
            {
                $description = trans('fi.transition.invoice.updated', ['invoice_number' => '#' . $detail->number]);
                $info        = (isset($detail->amount)) ? trans('fi.total') . ': ' . $detail->amount : '';
            }
            elseif ($this->action_type == 'credit_memo_updated')
            {
                $description = trans('fi.transition.invoice.credit_memo_updated', ['credit_memo_number' => '#' . $detail->number]);
                $info        = (isset($detail->amount)) ? trans('fi.total') . ': ' . $detail->amount : '';
            }
            elseif ($this->action_type == 'deleted')
            {
                $description = trans('fi.transition.invoice.deleted', ['invoice_number' => '#' . $detail->number]);
            }
            elseif ($this->action_type == 'credit_memo_deleted')
            {
                $description = trans('fi.transition.invoice.credit_memo_deleted', ['credit_memo_number' => '#' . $detail->number]);
            }
            elseif ($this->action_type == 'email_sent')
            {
                $description = trans('fi.transition.invoice.email_sent', ['invoice_number' => '#' . $detail->number]);
                $info        = (isset($detail->amount)) ? trans('fi.total') . ': ' . $detail->amount : '';
            }
            elseif ($this->action_type == 'email_opened')
            {
                $description = trans('fi.transition.invoice.email_opened', ['invoice_number' => '#' . $detail->number]);
                $info        = (isset($detail->amount)) ? trans('fi.total') . ': ' . $detail->amount : '';
            }
            elseif ($this->action_type == 'status_changed')
            {
                $description = trans('fi.transition.invoice.status_changed', ['invoice_number' => '#' . $detail->number, 'previous_value' => trans('fi.' . $this->previous_value), 'current_value' => trans('fi.' . $this->current_value)]);
                $info        = (isset($detail->amount)) ? trans('fi.total') . ': ' . $detail->amount : '';
            }

        }

        if ($this->transitionable_type == 'FI\Modules\Quotes\Models\Quote')
        {
            if ($this->action_type == 'created')
            {
                $description = trans('fi.transition.quote.created', ['quote_number' => '#' . $detail->number]);
                $info        = (isset($detail->amount)) ? trans('fi.total') . ': ' . $detail->amount : '';
            }
            elseif ($this->action_type == 'updated')
            {
                $description = trans('fi.transition.quote.updated', ['quote_number' => '#' . $detail->number]);
                $info        = (isset($detail->amount)) ? trans('fi.total') . ': ' . $detail->amount : '';
            }
            elseif ($this->action_type == 'deleted')
            {
                $description = trans('fi.transition.quote.deleted', ['quote_number' => '#' . $detail->number]);
            }
            elseif ($this->action_type == 'email_sent')
            {
                $description = trans('fi.transition.quote.email_sent', ['quote_number' => '#' . $detail->number]);
                $info        = (isset($detail->amount)) ? trans('fi.total') . ': ' . $detail->amount : '';
            }
            elseif ($this->action_type == 'email_opened')
            {
                $description = trans('fi.transition.quote.email_opened', ['quote_number' => '#' . $detail->number]);
                $info        = (isset($detail->amount)) ? trans('fi.total') . ': ' . $detail->amount : '';
            }
            elseif ($this->action_type == 'status_changed')
            {
                $description = trans('fi.transition.quote.status_changed', ['quote_number' => '#' . $detail->number, 'previous_value' => trans('fi.' . $this->previous_value), 'current_value' => trans('fi.' . $this->current_value)]);
                $info        = (isset($detail->amount)) ? trans('fi.total') . ': ' . $detail->amount : '';
            }
            elseif ($this->action_type == 'quote_to_invoice')
            {
                $description = trans('fi.transition.quote.quote_to_invoice', ['quote_number' => '#' . $detail->quote_number, 'invoice_number' => $detail->invoice_number]);
            }
        }

        if ($this->transitionable_type == 'FI\Modules\RecurringInvoices\Models\RecurringInvoice')
        {
            if ($this->action_type == 'created')
            {
                $description = trans('fi.transition.recurring_invoice.created', ['invoice_number' => '#' . $detail->number]);
                $info        = (isset($detail->amount)) ? trans('fi.total') . ': ' . $detail->amount : '';
            }
            elseif ($this->action_type == 'updated')
            {
                $description = trans('fi.transition.recurring_invoice.updated', ['invoice_number' => '#' . $detail->number]);
                $info        = (isset($detail->amount)) ? trans('fi.total') . ': ' . $detail->amount : '';
            }
            elseif ($this->action_type == 'deleted')
            {
                $description = trans('fi.transition.recurring_invoice.deleted', ['invoice_number' => '#' . $detail->number]);
            }
        }

        if ($this->transitionable_type == 'FI\Modules\Payments\Models\PaymentInvoice')
        {
            if ($this->action_type == 'payment_received')
            {
                $description = trans('fi.transition.invoice.payment_received', ['invoice_number' => '#' . $detail->invoice_number, 'full_payment_text' => ($detail->is_full_amount) ? trans('fi.full_and_final_payment') : trans('fi.partial')]);
                $info        = trans('fi.amount') . ': ' . $detail->invoice_amount_paid;
            }

            if ($this->action_type == 'payment_reversed')
            {
                $description = trans('fi.transition.invoice.payment_reversed', ['invoice_number' => '#' . $detail->invoice_number, 'full_payment_text' => ($detail->is_full_amount) ? trans('fi.full_payment_reversed') : trans('fi.partial_payment_reversed')]);
                $info        = trans('fi.amount') . ': ' . $detail->invoice_amount_paid;
            }
        }

        if ($this->transitionable_type == 'FI\Modules\Payments\Models\Payment')
        {
            if ($this->action_type == 'prepayment_created')
            {
                $description = trans('fi.transition.payment.prepayment_created');
                $info        = (isset($detail->amount)) ? trans('fi.total') . ': ' . $detail->amount : '';
            }
            if ($this->action_type == 'payment_receipt_email_sent')
            {
                $description = trans('fi.transition.payment.payment_receipt_email_sent');
                $info        = (isset($detail->amount)) ? trans('fi.total') . ': ' . $detail->amount : '';
            }
            if ($this->action_type == 'payment_updated')
            {
                $description = trans('fi.transition.payment.payment_updated');
                $info        = (isset($detail->amount)) ? trans('fi.total') . ': ' . $detail->amount : '';
            }
            if ($this->action_type == 'deleted')
            {
                $description = trans('fi.transition.payment.deleted');
                $info        = (isset($detail->amount)) ? trans('fi.total') . ': ' . $detail->amount : '';
            }
        }

        if ($this->transitionable_type == 'FI\Modules\Notes\Models\Note')
        {
            if ($this->action_type == 'created')
            {
                $description = trans('fi.transition.note.created');
                if (isset($detail->short_text) && str_word_count($detail->short_text) > 50)
                {
                    $info = '<div id="module" class="note-container">
                                <div class="collapse note-collapse" id="collapse' . $this->id . '" aria-expanded="false">' . $detail->short_text . '</div>
                                <a role="button" class="collapsed note-collapsed" data-toggle="collapse" href="#collapse' . $this->id . '" aria-expanded="false" aria-controls="#collapse' . $this->id . '">' . trans("fi.show_more") . '</a>
                                </div>';
                }
                elseif (isset($detail->short_text))
                {
                    $info = trans('fi.text') . ': ' . $detail->short_text;
                }
                else
                {
                    $info = '';
                }
            }
            if ($this->action_type == 'updated')
            {
                $description = trans('fi.transition.note.updated');
                if (isset($detail->short_text) && str_word_count($detail->short_text) > 50)
                {
                    $info = '<div id="module" class="note-container">
                                                <div class="collapse note-collapse" id="collapse' . $this->id . '" aria-expanded="false">' . $detail->short_text . '</div>
                                                <a role="button" class="collapsed note-collapsed" data-toggle="collapse" href="#collapse' . $this->id . '" aria-expanded="false" aria-controls="#collapse' . $this->id . '">' . trans("fi.show_more") . '</a>
                                                </div>';
                }
                elseif (isset($detail->short_text))
                {
                    $info = trans('fi.text') . ': ' . $detail->short_text;
                }
                else
                {
                    $info = '';
                }

            }
            if ($this->action_type == 'deleted')
            {
                $description = trans('fi.transition.note.deleted');
                $info        = (isset($detail->short_text)) ? trans('fi.text') . ': ' . utf8_encode($detail->short_text) : '';

            }
        }

        if ($this->transitionable_type == 'FI\Modules\Expenses\Models\Expense')
        {
            if ($this->action_type == 'created')
            {
                $description = trans('fi.transition.expense.created');
                $info        = (isset($detail->amount)) ? trans('fi.total') . ': ' . $detail->amount : '';
            }
            if ($this->action_type == 'updated')
            {
                $description = trans('fi.transition.expense.updated');
                $info        = (isset($detail->amount)) ? trans('fi.total') . ': ' . $detail->amount : '';
            }
            if ($this->action_type == 'deleted')
            {
                $description = trans('fi.transition.expense.deleted');
                $info        = (isset($detail->amount)) ? trans('fi.total') . ': ' . $detail->amount : '';
            }
            if ($this->action_type == 'billed')
            {
                $description = trans('fi.transition.expense.billed');
                $info        = (isset($detail->invoice)) ? trans('fi.invoice') . ': #' . $detail->invoice : '';
            }
        }

        if ($this->transitionable_type == 'FI\Modules\TaskList\Models\Task')
        {
            if ($this->action_type == 'created')
            {
                $description = trans('fi.transition.task.created');
                $title       = (isset($detail->short_title)) ? trans('fi.title') . ': ' . $detail->short_title : '';
                if (isset($detail->short_text) && str_word_count($detail->short_text) > 50)
                {
                    $info = '<div id="module" class="note-container">' . $title . '
                            <div class="collapse note-collapse" id="collapse' . $this->id . '" aria-expanded="false">' . $detail->short_text . '</div>
                            <a role="button" class="collapsed note-collapsed" data-toggle="collapse" href="#collapse' . $this->id . '" aria-expanded="false" aria-controls="#collapse' . $this->id . '">' . trans("fi.show_more") . '</a>
                            </div>';
                }
                elseif (isset($detail->short_text))
                {
                    $info = $title . '<br>' . trans('fi.text') . ': ' . $detail->short_text;
                }
                else
                {
                    $info = $title;
                }
            }
            if ($this->action_type == 'updated')
            {
                $description = trans('fi.transition.task.updated');
                $title        = (isset($detail->short_title)) ? trans('fi.title') . ': ' . $detail->short_title : '';
                if (isset($detail->short_text) && str_word_count($detail->short_text) > 50)
                {
                    $info = '<div id="module" class="note-container">' . $title . '
                                            <div class="collapse note-collapse" id="collapse' . $this->id . '" aria-expanded="false">' . trans('fi.description') . ': ' .$detail->short_text . '</div>
                                            <a role="button" class="collapsed note-collapsed" data-toggle="collapse" href="#collapse' . $this->id . '" aria-expanded="false" aria-controls="#collapse' . $this->id . '">' . trans("fi.show_more") . '</a>
                                            </div>';
                }
                elseif (isset($detail->short_text))
                {
                    $info = $title . '<br>' . trans('fi.description') . ': ' . $detail->short_text;
                }
                else
                {
                    $info = $title;
                }
            }
            if ($this->action_type == 'deleted')
            {
                $description = trans('fi.transition.task.deleted');
                $info        = (isset($detail->short_title)) ? trans('fi.title') . ': ' . $detail->short_title : '';
            }
            if ($this->action_type == 'completed')
            {
                $description = trans('fi.transition.task.completed');
                $info        = (isset($detail->short_title)) ? trans('fi.title') . ': ' . $detail->short_title : '';
            }
        }

        if ($this->transitionable_type == 'FI\Modules\Attachments\Models\Attachment')
        {
            if ($this->action_type == 'created')
            {
                $description = trans('fi.transition.attachment.created', ['filename' => $detail->filename]);

            }

            if ($this->action_type == 'deleted')
            {
                $description = trans('fi.transition.attachment.deleted', ['filename' => $detail->filename]);
            }

        }

        if ($description === '')
        {
            return $info;
        }
        else
        {
            return $description . '<br />' . $info;
        }


    }

    public function getTransitionEntityIconAttribute()
    {
        if ($this->transitionable_type == 'FI\Modules\Clients\Models\Client')
        {
            return 'fa fa-user';
        }
        if ($this->transitionable_type == 'FI\Modules\Invoices\Models\Invoice')
        {
            if ($this->action_type == 'email_sent')
            {
                return 'fa fa-envelope';
            }
            elseif ($this->action_type == 'email_opened')
            {
                return 'fa fa-envelope-o';
            }
            else
            {
                return 'fa fa-file-text';
            }

        }
        if ($this->transitionable_type == 'FI\Modules\Quotes\Models\Quote')
        {
            if ($this->action_type == 'email_sent')
            {
                return 'fa fa-envelope';
            }
            elseif ($this->action_type == 'email_opened')
            {
                return 'fa fa-envelope-o';
            }
            else
            {
                return 'fa fa-file-text-o';
            }

        }
        if ($this->transitionable_type == 'FI\Modules\Payments\Models\PaymentInvoice')
        {
            return 'fa fa-credit-card';
        }
        if ($this->transitionable_type == 'FI\Modules\Payments\Models\Payment')
        {
            return 'fa fa-money';
        }
        if ($this->transitionable_type == 'FI\Modules\Notes\Models\Note')
        {
            return 'fa fa-comments-o';
        }
        if ($this->transitionable_type == 'FI\Modules\TaskList\Models\Task')
        {
            return 'fa fa-tasks';
        }
        if ($this->transitionable_type == 'FI\Modules\Expenses\Models\Expense')
        {
            return 'fa fa-credit-card';
        }
        if ($this->transitionable_type == 'FI\Modules\RecurringInvoices\Models\RecurringInvoice')
        {
            return 'fa fa-refresh';
        }
        if ($this->transitionable_type == 'FI\Modules\Attachments\Models\Attachment')
        {
            return 'fa fa-paperclip';
        }
    }

    public function getTransitionEntityNameAttribute()
    {
        if ($this->transitionable_type == 'FI\Modules\Clients\Models\Client')
        {
            return trans('fi.client');
        }
        if ($this->transitionable_type == 'FI\Modules\Invoices\Models\Invoice')
        {
            if (($this->transitionable) && ($this->transitionable->type == 'credit_memo'))
            {
                return trans('fi.credit_memo');
            }
            else
            {
                return trans('fi.invoice');
            }
        }
        if ($this->transitionable_type == 'FI\Modules\Quotes\Models\Quote')
        {
            return trans('fi.quote');
        }
        if ($this->transitionable_type == 'FI\Modules\Payments\Models\PaymentInvoice')
        {
            if (($this->transitionable) && ($this->transitionable->payment->credit_memo_id))
            {
                return trans('fi.credit_applied');
            }
            else
            {
                return trans('fi.payments');
            }
        }
        if ($this->transitionable_type == 'FI\Modules\Payments\Models\Payment')
        {
            return trans('fi.payments');
        }
        if ($this->transitionable_type == 'FI\Modules\Notes\Models\Note')
        {
            return trans('fi.note');
        }
        if ($this->transitionable_type == 'FI\Modules\TaskList\Models\Task')
        {
            return trans('fi.task');
        }
        if ($this->transitionable_type == 'FI\Modules\Expenses\Models\Expense')
        {
            return trans('fi.expense');
        }
        if ($this->transitionable_type == 'FI\Modules\RecurringInvoices\Models\RecurringInvoice')
        {
            return trans('fi.recurring_invoice');
        }
        if ($this->transitionable_type == 'FI\Modules\Attachments\Models\Attachment')
        {
            return trans('fi.attachment');
        }
    }

    public function getFormattedActionTypeAttribute()
    {
        $actionType = explode('_', $this->action_type);

        if ($this->action_type == 'quote_to_invoice')
        {
            return trans('fi.transition.' . $this->action_type);
        }
        else
        {
            return isset($actionType[1]) ? trans('fi.transition.' . end($actionType)) : trans('fi.transition.' . $actionType[0]);
        }
    }

    public function getFormattedActionCountAttribute()
    {
        $detail = json_decode($this->detail);

        return isset($detail->action_count) && $detail->action_count > 0 ? $detail->action_count : '';
    }

    public static function getModulesList()
    {
        $moduleMap  = self::mapModule();
        $moduleList = [];
        foreach ($moduleMap as $key => $module)
        {
            $moduleList[$key] = trans('fi.' . $key);
        }
        return $moduleList;
    }

    public static function mapModule()
    {
        return [
            'attachment'         => 'FI\Modules\Attachments\Models\Attachment',
            'clients'            => 'FI\Modules\Clients\Models\Client',
            'credit_applied'     => 'FI\Modules\Payments\Models\PaymentInvoice',
            'credit_memo'        => 'FI\Modules\Invoices\Models\Invoice',
            'expenses'           => 'FI\Modules\Expenses\Models\Expense',
            'invoices'           => 'FI\Modules\Invoices\Models\Invoice',
            'notes'              => 'FI\Modules\Notes\Models\Note',
            'payments'           => 'FI\Modules\Payments\Models\Payment',
            'quotes'             => 'FI\Modules\Quotes\Models\Quote',
            'recurring_invoices' => 'FI\Modules\RecurringInvoices\Models\RecurringInvoice',
            'tasks'              => 'FI\Modules\TaskList\Models\Task',
        ];
    }

    public function scopeModules($query, $modules = [])
    {
        $moduleMap = self::mapModule();

        $query->where(function ($q) use ($modules, $moduleMap)
        {
            foreach ($modules as $module)
            {
                if ($module == 'credit_memo')
                {
                    $q->orWhere('action_type', 'like', 'credit_memo%');
                }
                elseif ($module == 'invoices')
                {
                    $q->orWhere([
                        ['transitionable_type', $moduleMap[$module]],
                        ['action_type', 'not like', 'credit_memo%'],
                    ]);
                }
                else
                {
                    $q->orWhere('transitionable_type', $moduleMap[$module]);
                }
            }
        });

        return $query;
    }

    public static function getPaginatedTransitions($filterUsers = [], $filterModules = [], $customSearch = null, $clientId = null)
    {
        $q = Transitions::query()->modules($filterModules);

        if ($clientId)
        {
            $q->where('client_id', $clientId);
        }
        if ((!empty($filterUsers)) && ($filterUsers[0] != ''))
        {
            $q->whereIn('user_id', $filterUsers);
        }
        elseif (auth()->user()->user_type == 'standard_user')
        {
            $q->where('user_id', auth()->user()->id);
        }

        if ($customSearch)
        {
            $q->where('detail', 'like', '%' . $customSearch . '%');
        }

        return $q->orderBy('created_at', 'desc')
            ->groupBy('id')
            ->paginate(config('fi.resultsPerPage'));
    }

}