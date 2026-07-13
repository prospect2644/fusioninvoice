<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Modules\Transitions\Controllers;

use FI\Modules\Clients\Models\Client;
use FI\Modules\Transitions\Models\Transitions;
use FI\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TransitionController extends Controller
{

    public function userTransitions(Request $request, Client $client)
    {
        $filterUsers   = ($request->has('user') && !empty($request->get('user'))) ? $request->get('user') : [];
        $filterModules = ($request->has('filter_module') && !empty($request->get('filter_module'))) ? $request->get('filter_module') : [];
        $customSearch  = ($request->has('custom_search') && !empty($request->get('custom_search'))) ? $request->get('custom_search') : null;
        $clientId      = $client->id;

        if ($customSearch && empty($filterModules))
        {
            $filterModules = array_keys(Transitions::mapModule());
        }

        $transitions          = Transitions::getPaginatedTransitions($filterUsers, $filterModules, $customSearch, $clientId);
        $monthWiseTransitions = [];

        foreach ($transitions as $transition)
        {
            $key                          = trans(strtolower('fi.month_' . $transition->created_at->format('F'))) . ' ' . $transition->created_at->format('Y');
            $monthWiseTransitions[$key][] = $transition;
        }
        return view('transitions.list')
            ->with('transitions', $transitions)
            ->with('monthWiseTransitions', $monthWiseTransitions)
            ->with('hideHeader', true);
    }

    public function widgetList(Request $request)
    {
        $filterUsers   = ($request->has('user') && !empty($request->get('user'))) ? $request->get('user') : [];
        $filterModules = ($request->has('filter_module') && !empty($request->get('filter_module'))) ? $request->get('filter_module') : [];
        $customSearch  = ($request->has('custom_search') && !empty($request->get('custom_search'))) ? $request->get('custom_search') : null;
        if ($customSearch && empty($filterModules))
        {
            $filterModules = array_keys(Transitions::mapModule());
        }

        $transitions          = Transitions::getPaginatedTransitions($filterUsers, $filterModules, $customSearch);
        $monthWiseTransitions = [];

        foreach ($transitions as $transition)
        {
            $key                          = trans(strtolower('fi.month_' . $transition->created_at->format('F'))) . ' ' . $transition->created_at->format('Y');
            $monthWiseTransitions[$key][] = $transition;
        }
        return view('transitions.widget.list')
            ->with('transitions', $transitions)
            ->with('monthWiseTransitions', $monthWiseTransitions)
            ->with('hideHeader', true);
    }
}
