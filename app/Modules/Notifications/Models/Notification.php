<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Modules\Notifications\Models;

use FI\Modules\TaskList\Models\Task;
use FI\Support\DateFormatter;
use FI\Traits\Sortable;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use Sortable;

    protected $table = 'notifications';

    protected $guarded = ['id'];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function notifiable()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo('FI\Modules\Users\Models\User')->withTrashed();
    }

    public function task()
    {
        return $this->belongsTo(Task::class, 'notifiable_id')
            ->where('notifiable_type', Task::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    public function getFormattedCreatedAtAttribute()
    {
        return DateFormatter::formatTimeAgo($this->created_at, true);
    }

    public function getFormattedUpdatedAtAttribute()
    {
        return DateFormatter::formatTimeAgo($this->updated_at, true);
    }

    public function getFormattedViewedAtAttribute()
    {
        return DateFormatter::formatTimeAgo($this->viewed_at, true);
    }

    public function getNotificationDetailAttribute()
    {
        $text = [];
        if ($this->notifiable_type == 'FI\Modules\TaskList\Models\Task')
        {
            if ($this->action_type == 'created')
            {
                $text['info']  = trans('fi.notification.task.created');
                $text['title'] = $this->notifiable->title;
                $text['url']   = url('/');
                $text['icon']  = 'fa-tasks';
            }
        }
        return $text;
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */
    public function scopeUserId($query, $user_id)
    {
        return $query->where('user_id', $user_id);
    }

}