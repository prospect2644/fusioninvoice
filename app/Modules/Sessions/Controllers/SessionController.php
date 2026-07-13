<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Modules\Sessions\Controllers;

use FI\Http\Controllers\Controller;
use FI\Jobs\PostLogin;
use FI\Modules\Sessions\Requests\SessionRequest;
use FI\Modules\Users\Models\User;

class SessionController extends Controller
{
    public function login()
    {
        deleteTempFiles();
        deleteViewCache();
        return view('sessions.login');
    }

    public function attempt(SessionRequest $request)
    {
        $rememberMe = ($request->input('remember_me')) ? true : false;

        $user = User::whereEmail($request->input('email'))->where('user_type', '<>', 'system')->first();

        if (isset($user))
        {
            if ($user->status == 0)
            {
                return redirect()->route('session.login')->with('alert', trans('fi.user_not_active'));
            }

            if (!auth()->attempt(['email' => $request->input('email'), 'password' => $request->input('password'), 'status' => 1], $rememberMe))
            {
                return redirect()->route('session.login')->with('error', trans('fi.invalid_credentials'));
            }

            PostLogin::dispatch(auth()->user());

            if (!auth()->user()->client_id)
            {
                return redirect()->route('dashboard.index');
            }

            return redirect()->route('clientCenter.dashboard');
        }
        else
        {
            return redirect()->route('session.login')->with('error', trans('fi.invalid_credentials'));
        }

    }

    public function logout()
    {
        auth()->logout();

        session()->flush();

        return redirect()->route('session.login');
    }

    public function refreshCaptcha()
    {
        return captcha_img('math');
    }
}