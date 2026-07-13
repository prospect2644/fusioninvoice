<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Modules\Notes\Models;

use Carbon\Carbon;
use FI\Support\DateFormatter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use FI\Support\HTML;

class Note extends Model
{
    protected $table = 'notes';

    protected $guarded = ['id'];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function notable()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo('FI\Modules\Users\Models\User')->withTrashed();
    }

    public function updatedBy()
    {
        return $this->belongsTo('FI\Modules\Users\Models\User', 'updated_by');
    }

    public function tags()
    {
        return $this->hasMany('FI\Modules\Notes\Models\NoteTag');
    }

    public function transitions()
    {
        return $this->morphMany('FI\Modules\Transitions\Models\Transitions', 'transitionable');
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    public function getFormattedCreatedAtAttribute()
    {
        if (Carbon::parse($this->created_at)->diffInDays(Carbon::now()) <= 7)
        {
            Carbon::setLocale(app()->getLocale());
            return Carbon::parse($this->created_at)->diffForHumans();
        }
        else
        {
            return DateFormatter::format($this->created_at, true);
        }

    }

    public function getFormattedCreatedAtSystemFormatAttribute()
    {
        return DateFormatter::format($this->created_at, true);
    }

    public function getFormattedUpdatedAtAttribute()
    {
        if (Carbon::parse($this->updated_at)->diffInDays(Carbon::now()) <= 7)
        {
            Carbon::setLocale(app()->getLocale());
            return Carbon::parse($this->updated_at)->diffForHumans();
        }
        else
        {
            return DateFormatter::format($this->updated_at, true);
        }
    }

    public function getFormattedUpdatedAtSystemFormatAttribute()
    {
        return DateFormatter::format($this->updated_at, true);
    }

    public function getFormattedNoteAttribute()
    {
        if (str_word_count(trim($this->note)) > 50)
        {
            return '<div id="module" class="note-container">
                                        <div class="collapse note-collapse" id="collapse' . $this->id . '" aria-expanded="false">' . nl2br(trim($this->note)) . '</div>
                                        <a role="button" class="collapsed note-collapsed" data-toggle="collapse" href="#collapse' . $this->id . '" aria-expanded="false" aria-controls="#collapse' . $this->id . '">' . trans("fi.show_more") . '</a>
                                        </div>';
        }
        else
        {
            return nl2br($this->note);
        }

    }

    public function deleteTags(Note $note)
    {
        $note->tags()->delete();
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeProtect($query, $user)
    {
        if ($user->client_id)
        {
            return $query->where('private', 0);
        }

    }

    public function scopeKeywords($query, $keywords, $description, $tags, $username)
    {
        if ($keywords)
        {
            $query->leftJoin('users', 'notes.user_id', '=', 'users.id')
                ->leftJoin('note_tags', 'notes.id', '=', 'note_tags.note_id')
                ->leftJoin('tags', 'note_tags.tag_id', '=', 'tags.id');

            $query->where(function ($q) use ($keywords, $description, $tags, $username)
            {
                if ($description)
                {
                    $q->orWhereRaw("CONCAT_WS('^',LOWER(note) LIKE ?)", ['%' . $keywords . '%']);
                }
                if ($username)
                {
                    $q->orWhereRaw("CONCAT_WS('^',LOWER(users.name) LIKE ?)", ['%' . $keywords . '%']);
                }
                if ($tags)
                {
                    $q->orWhereRaw("CONCAT_WS('^',LOWER(tags.name) LIKE ?)", ['%' . $keywords . '%']);
                }
            });
        }

        return $query;
    }
}