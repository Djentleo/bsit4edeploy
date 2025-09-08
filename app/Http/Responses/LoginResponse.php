<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Illuminate\Http\Request;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request)
    {
        $user = $request->user();
        // Redirect responders to their own page
        if ($user && $user->role === 'responder') {
            return redirect()->intended('/responder/incidents');
        }
        // Default: go to dashboard
        return redirect()->intended('/dashboard');
    }
}
