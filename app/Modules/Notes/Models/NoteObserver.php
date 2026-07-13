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

use FI\Modules\Notes\Events\AddTransition;

class NoteObserver
{
    public function deleted(Note $note)
    {
        foreach ($note->tags as $tag)
        {
            $tag->delete();
        }
    }

    public function created(Note $note)
    {
        event(new AddTransition($note, 'created'));
    }

    public function updated(Note $note)
    {
        event(new AddTransition($note, 'updated'));
    }
}