<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Modules\TaskList\Controllers;

use Carbon\Carbon;
use FI\Http\Controllers\Controller;
use FI\Modules\Clients\Models\Client;
use FI\Modules\TaskList\Models\Task;
use FI\Modules\TaskList\Models\TaskSection;
use FI\Modules\TaskList\Requests\TaskReorderRequest;
use FI\Modules\TaskList\Requests\TaskStoreRequest;
use FI\Modules\TaskList\Requests\TaskUpdateRequest;
use FI\Modules\Users\Models\User;
use FI\Traits\ReturnUrl;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Session;

class TaskController extends Controller
{
    use ReturnUrl;

    public function index()
    {
        $this->setReturnUrl();

        $dateRangeFrom = request('date_range_filter_from', null);
        $dateRangeTo   = request('date_range_filter_to', null);
        $tasks         = Task::select('*')
            ->keywords(request('search'), true, true, true, true)
            ->status(request('status', 'open'))
            ->ownTasks(auth()->user()->id)
            ->dateRange($dateRangeFrom, $dateRangeTo)
            ->sortable(['task_section_id' => 'asc'])
            ->paginate(config('fi.defaultNumPerPage'));

        return view('tasks.index')
            ->with('tasks', $tasks)
            ->with('me', auth()->user())
            ->with('users', User::all()->whereNotIn('user_type', ['client', 'system'])->pluck('name', 'id'))
            ->with('client', request('client'))
            ->with('searchPlaceholder', trans('fi.search_tasks'))
            ->with('statuses', ['all' => trans('fi.all_statuses'), 'closed' => trans('fi.closed'), 'overdue' => trans('fi.overdue'), 'open' => trans('fi.open')])
            ->with('clients', Client::all()->pluck('name', 'id'));

    }

    public function taskList()
    {
        $filterBy            = request('filterBy', []);
        $filterByDescription = $filterBy['description'] ?? 0;
        $filterByTitle       = $filterBy['title'] ?? 0;
        $filterByClient      = $filterBy['client'] ?? 0;
        $filterByAssignee    = $filterBy['assignee'] ?? 0;
        $dateRangeFrom       = request('date_range_filter_from', null);
        $dateRangeTo         = request('date_range_filter_to', null);
        $filterByDescription == 1 ? Session::put('filter_by_task_description', 1) : Session::put('filter_by_task_description', 0);
        $filterByTitle == 1 ? Session::put('filter_by_title', 1) : Session::put('filter_by_title', 0);
        $filterByClient == 1 ? Session::put('filter_by_client', 1) : Session::put('filter_by_client', 0);
        $filterByAssignee == 1 ? Session::put('filter_by_assignee', 1) : Session::put('filter_by_assignee', 0);

        /** @var Collection $tasksCollection */
        $tasksCollection = Task::select('tasks.*')->with(['assignee', 'client', 'taskSection'])
            ->keywords(request('search'), $filterByDescription, $filterByTitle, $filterByClient, $filterByAssignee)
            ->ownTasks(auth()->user()->id)
            ->status(request('status', 'open'))
            ->assignee(request('assignee', null))
            ->sortable(['task_section_id' => 'asc'])
            ->dateRange($dateRangeFrom, $dateRangeTo)
            ->get();

        $tasks = [];
        /** @var Task $task */
        $tasksCollection->each(function ($task) use (&$tasks)
        {
            $taskSection = $task->taskSection;
            if (empty($tasks[$taskSection->id]))
            {
                $tasks[$taskSection->id] = [
                    'sectionId'   => $taskSection->id,
                    'sectionName' => trans('fi.' . $taskSection->slug),
                    'tasks'       => [],
                ];
            }

            $tasks[$taskSection->id]['tasks'][] = $task;
        });

        $sections = TaskSection::all()->pluck('slug', 'id');

        foreach ($sections as $sectionId => $sectionSlug)
        {
            if (!empty($tasks[$sectionId]['tasks']))
            {
                usort($tasks[$sectionId]['tasks'], function ($item1, $item2)
                {
                    return $item1->sequence <=> $item2->sequence;
                });
            }
            else
            {
                $tasks[$sectionId] = [
                    'sectionId'   => $sectionId,
                    'sectionName' => trans('fi.' . $sectionSlug),
                    'tasks'       => [],
                ];
            }
        }
        sort($tasks);
        return view('tasks.widget.list')
            ->with('tasks', $tasks)
            ->with('users', User::all()->whereNotIn('user_type', ['client', 'system'])->pluck('name', 'id'))
            ->with('client', request('client'))
            ->with('clients', Client::all()->pluck('name', 'id'));
    }

    public function createWidget()
    {
        return view('tasks.widget.create_edit_modal')
            ->with('editMode', false)
            ->with('users', User::all()->whereNotIn('user_type', ['client', 'system'])->pluck('name', 'id'))
            ->with('taskSections', TaskSection::all()->pluck('name', 'id'))
            ->with('client', request('client'))
            ->with('clients', Client::all()->pluck('name', 'id'));
    }

    public function storeWidget(TaskStoreRequest $request)
    {
        Task::create([
            'user_id'         => auth()->user()->id,
            'title'           => $request->post('title'),
            'description'     => $request->post('description'),
            'due_date'        => $request->post('due_date_timestamp'),
            'assignee_id'     => $request->post('assignee_id'),
            'client_id'       => $request->post('client_id'),
            'task_section_id' => $request->post('task_section_id'),
        ]);

        return response()->json([
            'message' => trans('fi.task_successfully_created'),
        ]);
    }

    public function editWidget($id)
    {
        $user_id = auth()->user()->id;

        $task = Task::whereId($id)->where(function ($query) use ($user_id)
        {
            $query->orWhere('user_id', $user_id)->orWhere('assignee_id', $user_id);
        })->first();

        return view('tasks.widget.create_edit_modal')
            ->with('editMode', true)
            ->with('task', $task)
            ->with('taskSections', TaskSection::all()->pluck('name', 'id'))
            ->with('users', User::all()->whereNotIn('user_type', ['client', 'system'])->pluck('name', 'id'))
            ->with('clients', Client::all()->pluck('name', 'id'));
    }

    public function updateWidget(TaskUpdateRequest $request, $id)
    {
        $task                  = Task::find($id);
        $task->title           = $request->post('title');
        $task->description     = $request->post('description');
        $task->due_date        = $request->post('due_date_timestamp') != '' ? $request->post('due_date_timestamp') : null;
        $task->assignee_id     = $request->post('assignee_id');
        $task->client_id       = $request->post('client_id');
        $task->task_section_id = $request->post('task_section_id');

        $task->save();

        return response()->json([
            'message' => trans('fi.task_successfully_updated'),
        ]);
    }

    public function create()
    {
        return view('tasks.form')
            ->with('editMode', false)
            ->with('users', User::all()->whereNotIn('user_type', ['client', 'system'])->pluck('name', 'id'))
            ->with('taskSections', TaskSection::all()->pluck('name', 'id'))
            ->with('client', request('client'))
            ->with('returnUrl', $this->getReturnUrl())
            ->with('clients', Client::all()->pluck('name', 'id'));
    }

    public function store(TaskStoreRequest $request)
    {
        $task = Task::create([
            'user_id'         => auth()->user()->id,
            'title'           => $request->post('title'),
            'description'     => $request->post('description'),
            'due_date'        => $request->post('due_date_timestamp'),
            'assignee_id'     => $request->post('assignee_id'),
            'client_id'       => $request->post('client_id'),
            'task_section_id' => $request->post('task_section_id'),
        ]);

        return response()->json([
            'message' => trans('fi.task_successfully_created'),
            'task_id' => $task->id,
        ]);
    }

    public function edit($id)
    {
        $user_id = auth()->user()->id;

        $task = Task::whereId($id)->where(function ($query) use ($user_id)
        {
            $query->orWhere('user_id', $user_id)->orWhere('assignee_id', $user_id);
        })->first();

        return view('tasks.form')
            ->with('editMode', true)
            ->with('task', $task)
            ->with('taskSections', TaskSection::all()->pluck('name', 'id'))
            ->with('users', User::all()->whereNotIn('user_type', ['client', 'system'])->pluck('name', 'id'))
            ->with('returnUrl', $this->getReturnUrl())
            ->with('clients', Client::all()->pluck('name', 'id'));
    }

    public function update(TaskUpdateRequest $request, $id)
    {
        $task                  = Task::find($id);
        $task->title           = $request->post('title');
        $task->description     = $request->post('description');
        $task->due_date        = $request->post('due_date_timestamp');
        $task->assignee_id     = $request->post('assignee_id');
        $task->client_id       = $request->post('client_id');
        $task->task_section_id = $request->post('task_section_id');

        $task->save();

        return response()->json([
            'message' => trans('fi.task_successfully_updated'),
            'task_id' => $task->id,
        ]);
    }

    public function show($id)
    {
        $user_id = auth()->user()->id;

        $task = Task::whereId($id)->where(function ($query) use ($user_id)
        {
            $query->orWhere('user_id', $user_id)->orWhere('assignee_id', $user_id);
        })->first();

        return view('tasks.view')
            ->with('me', auth()->user())
            ->with('returnUrl', $this->getReturnUrl())
            ->with('task', $task);
    }

    public function completeToggle($id, $complete)
    {
        $task               = Task::find($id);
        $task->is_complete  = $complete;
        $task->completed_at = Carbon::now();
        $task->save();

        return response()->json([
            'message' => ($complete) ? trans('fi.task_completed') : trans('fi.task_marked_incomplete'),
        ]);
    }

    public function delete($taskId)
    {
        $user_id = auth()->user()->id;
        $task    = Task::whereId($taskId)->where(function ($query) use ($user_id)
        {
            $query->orWhere('user_id', $user_id)->orWhere('assignee_id', $user_id);

        })->first();

        if ($task->id)
        {
            $task->destroy($task->id);
        }
    }

    public function reorder(TaskReorderRequest $request)
    {
        $ids = $request->get('ids');
        foreach ($ids as $key => $id)
        {
            Task::whereId($id)->update(['sequence' => $key, 'task_section_id' => $request->get('task_section_id')]);
        }
    }

    public function refresh()
    {
        $sections = TaskSection::all();
        $later    = $today = $tomorrow = '';
        foreach ($sections as $section)
        {
            if ($section->slug == 'later')
            {
                $later = $section->id;
            }
            elseif ($section->slug == 'today')
            {
                $today = $section->id;
            }
            elseif ($section->slug == 'tomorrow')
            {
                $tomorrow = $section->id;
            }
        }
        $tasks = Task::select('tasks.*')->taskSections([$later, $tomorrow])->get();
        foreach ($tasks as $task)
        {
            if (Carbon::parse($task->due_date)->format('Y-m-d') == Carbon::now()->format('Y-m-d'))
            {
                $task->task_section_id = $today;
                $task->save();
            }
            elseif (Carbon::parse($task->due_date)->format('Y-m-d') == Carbon::now()->addDay()->format('Y-m-d'))
            {
                $task->task_section_id = $tomorrow;
                $task->save();
            }
        }
    }

    public function orderBy(Request $request)
    {
        $tasksCollection = Task::select('tasks.*')->with(['assignee', 'client', 'taskSection'])
            ->ownTasks(auth()->user()->id)
            ->where('task_section_id', $request->get('sectionId'))
            ->status(request('status', 'open'))
            ->orderBy('due_date', $request->get('dir'))
            ->get();

        $tasks = [];
        /** @var Task $task */
        $tasksCollection->each(function ($task) use (&$tasks)
        {
            $taskSection = $task->taskSection;
            if (empty($tasks[$taskSection->id]))
            {
                $tasks[$taskSection->id] = [
                    'sectionId'   => $taskSection->id,
                    'sectionName' => trans('fi.' . $taskSection->slug),
                    'tasks'       => [],
                ];
            }

            $tasks[$taskSection->id]['tasks'][] = $task;
        });

        return view('tasks.widget._task_sorting')
            ->with('tasks', $tasks)
            ->with('users', User::all()->whereNotIn('user_type', ['client', 'system'])->pluck('name', 'id'))
            ->with('client', request('client'))
            ->with('clients', Client::all()->pluck('name', 'id'));
    }
}