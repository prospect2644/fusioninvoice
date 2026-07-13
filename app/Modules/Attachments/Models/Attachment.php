<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Modules\Attachments\Models;

use FI\Modules\Clients\Models\Client;
use FI\Modules\Expenses\Models\Expense;
use FI\Modules\Invoices\Models\Invoice;
use FI\Modules\Quotes\Models\Quote;
use FI\Modules\TaskList\Models\Task;
use FI\Support\DateFormatter;
use Illuminate\Database\Eloquent\Model;

class Attachment extends Model
{
    protected $table = 'attachments';

    protected $guarded = ['id'];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function attachable()
    {
        return $this->morphTo();
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'attachable_id')
            ->where('attachable_type', Client::class);
    }

    public function expense()
    {
        return $this->belongsTo(Expense::class, 'attachable_id')
            ->where('attachable_type', Expense::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'attachable_id')
            ->where('attachable_type', Invoice::class);
    }

    public function quote()
    {
        return $this->belongsTo(Quote::class, 'attachable_id')
            ->where('attachable_type', Quote::class);
    }

    public function user()
    {
        return $this->belongsTo('FI\Modules\Users\Models\User');
    }

    public function task()
    {
        return $this->belongsTo(Task::class, 'attachable_id')
            ->where('attachable_type', Task::class);
    }
    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    public function getDownloadUrlAttribute()
    {
        return route('attachments.download', [$this->url_key]);
    }

    public function getFormattedCreatedAtAttribute()
    {
        return DateFormatter::format($this->created_at, true);
    }
}