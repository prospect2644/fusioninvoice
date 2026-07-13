<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Modules\Notes\Controllers;

use FI\Http\Controllers\Controller;
use FI\Modules\Clients\Models\Client;
use FI\Modules\Invoices\Models\Invoice;
use FI\Modules\Notes\Events\AddTransition;
use FI\Modules\Notes\Models\Note;
use FI\Modules\Notes\Requests\NoteRequest;
use FI\Modules\Payments\Models\Payment;
use FI\Modules\Quotes\Models\Quote;
use FI\Modules\Tags\Models\Tag;
use FI\Modules\TaskList\Models\Task;
use Session;

class NoteController extends Controller
{
    public function create($showPrivateCheckbox = 'no')
    {
        $tags = Tag::where('tag_entity', '=', 'note')->pluck('name', 'name');

        return view('notes._notes_modal_create')
            ->with('editMode', false)
            ->with('note', '')
            ->with('isPrivate', false)
            ->with('selectedTags', [])
            ->with('noteId', null)
            ->with('tags', $tags)
            ->with('showPrivateCheckbox', 'yes' == $showPrivateCheckbox ? true : false);
    }

    public function store(NoteRequest $request)
    {

        $model = base64_decode(request('model'));

        $object = $model::find(request('model_id'));

        $user_id = auth()->user()->id;

        $note = $object->notes()->create(['note' => request('note'), 'user_id' => $user_id, 'private' => request('isPrivate')]);

        // Is task from note is selected then we have to create task from note
        if (request('create_task'))
        {
            $this->createTask($object, $user_id);
        }

        if (request('isTimeLine'))
        {
            $tags    = request('tags', []);
            $tag_ids = [];

            if (is_array($tags))
            {
                foreach ($tags as $tag)
                {
                    $tag = Tag::firstOrNew(['name' => $tag], ['tag_entity' => 'note'])->fill(['name' => $tag, 'tag_entity' => 'note']);

                    $tag->save();

                    $tag_ids[] = $tag->id;
                }
            }

            $note->deleteTags($note);

            foreach ($tag_ids as $tag_id)
            {
                $note->tags()->create(['note_id' => $note->id, 'tag_id' => $tag_id]);
            }
            $notes = $object->notes()
                ->protect(auth()->user())
                ->with(['user', 'updatedBy'])
                ->orderBy('created_at', 'desc')
                ->paginate(config('fi.resultsPerPage'))
                ->setPath(route('notes.list', [request('model'), $object->id, (int)request('isPrivate')]));

            return view('notes._notes_timeline')
                ->with('model', request('model'))
                ->with('object', $object)
                ->with('notes', $notes)
                ->with('hideHeader', true)
                ->with('showPrivateCheckbox', request('showPrivateCheckbox'));
        }

        return view('notes._notes_list')
            ->with('object', $object)
            ->with('showPrivateCheckbox', request('showPrivateCheckbox'));
    }

    public function edit($noteId)
    {
        $note = Note::with(['tags.tag'])->find($noteId);

        $selectedTags = [];

        foreach ($note->tags as $tagDetail)
        {
            $selectedTags[] = $tagDetail->tag->name;
        }

        return view('notes._notes_modal_create')
            ->with('editMode', true)
            ->with('note', $note->note)
            ->with('isPrivate', $note->private)
            ->with('noteId', $note->id)
            ->with('tags', Tag::where('tag_entity', '=', 'note')->pluck('name', 'name'))
            ->with('selectedTags', $selectedTags)
            ->with('showPrivateCheckbox', true);
    }

    public function update(NoteRequest $request, $noteId)
    {
        $model = base64_decode(request('model'));

        $object = $model::find(request('model_id'));

        $note             = Note::with(['tags.tag'])->find($noteId);
        $note->note       = request('note');
        $note->private    = request('isPrivate');
        $note->updated_by = auth()->user()->id;
        $note->save();

        if (request('isTimeLine'))
        {
            $tags    = request('tags');
            $tag_ids = [];

            $note->deleteTags($note);

            if (is_array($tags))
            {
                foreach ($tags as $tag)
                {
                    $tag = Tag::firstOrNew(['name' => $tag], ['tag_entity' => 'note'])->fill(['name' => $tag, 'tag_entity' => 'note']);

                    $tag->save();

                    $tag_ids[] = $tag->id;
                }

                foreach ($tag_ids as $tag_id)
                {
                    $note->tags()->create(['note_id' => $note->id, 'tag_id' => $tag_id]);
                }
            }

            $notes = $object->notes()
                ->protect(auth()->user())
                ->with(['user', 'updatedBy'])
                ->orderBy('created_at', 'desc')
                ->paginate(config('fi.resultsPerPage'))
                ->setPath(route('notes.list', [request('model'), $object->id, (int)request('isPrivate')]));

            return view('notes._notes_timeline')
                ->with('model', request('model'))
                ->with('object', $object)
                ->with('notes', $notes)
                ->with('hideHeader', true)
                ->with('showPrivateCheckbox', request('showPrivateCheckbox'));
        }
        return view('notes._notes_list')
            ->with('object', $object)
            ->with('showPrivateCheckbox', request('showPrivateCheckbox'));
    }

    public function delete()
    {
        $note = Note::find(request('id'));
        event(new AddTransition($note, 'deleted'));
        Note::destroy(request('id'));
    }

    public function listNotes($model, $id, $showPrivateCheckbox)
    {
        request('description', '') == 1 ? Session::put('filter_by_description', 1) : Session::put('filter_by_description', 0);
        request('tags', '') == 1 ? Session::put('filter_by_tags', 1) : Session::put('filter_by_tags', 0);
        request('username', '') == 1 ? Session::put('filter_by_username', 1) : Session::put('filter_by_username', 0);

        $object = base64_decode($model)::find($id);

        $notes = Note::select('notes.*')->whereNotableId($id)
            ->whereNotableType(base64_decode($model))
            ->protect(auth()->user())
            ->with(['user', 'updatedBy'])
            ->keywords(request('search'), request('description'), request('tags'), request('username'))
            ->orderBy('notes.created_at', 'desc')
            ->groupBy('notes.id')
            ->paginate(config('fi.resultsPerPage'))
            ->appends('search', request('search'))
            ->appends('description', request('description'))
            ->appends('tags', request('tags'))
            ->appends('username', request('username'));

        return view('notes._notes_timeline')
            ->with('model', $model)
            ->with('object', $object)
            ->with('notes', $notes)
            ->with('hideHeader', true)
            ->with('showPrivateCheckbox', $showPrivateCheckbox);
    }

    public function createTask($object, $user_id)
    {
        if ($object instanceof Client)
        {
            $client_id = $object->id;
        }
        elseif ($object instanceof Invoice)
        {
            $client_id = $object->client->id;
        }
        elseif ($object instanceof Quote)
        {
            $client_id = $object->client->id;
        }
        elseif ($object instanceof Payment)
        {
            $client_id = $object->invoice->client->id;
        }
        elseif ($object instanceof Task)
        {
            $client_id = $object->client_id;
        }

        Task::create([
            'user_id'     => $user_id,
            'title'       => request('title') != '' ? request('title') : substr(request('note'), 0, 40),
            'description' => request('note'),
            'due_date'    => request('due_date_timestamp') ? request('due_date_timestamp') : null,
            'assignee_id' => $user_id,
            'client_id'   => $client_id,
        ]);

    }
}