<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) Sqware Pig, LLC <hello@squarepiginteractive.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Modules\API\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;

class ApiAuthController extends ApiController
{

    public function login(Request $request)
    {

        $validator = $this->validator->make($request->all(), [
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails())
        {
            return response()->json(['error' => $validator->errors()], 401);
        }

        if (Auth::attempt(['email' => request('email'), 'password' => request('password'), 'status' => 1, 'user_type' => 'admin']))
        {
            return response()->json(['token' => Auth::user()->createToken('FI')->accessToken], 200);
        }
        else
        {
            return response()->json(['error' => 'Unauthorised'], 401);
        }
    }

}