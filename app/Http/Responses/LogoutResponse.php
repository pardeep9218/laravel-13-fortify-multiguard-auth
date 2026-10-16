<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LogoutResponse as Contract;

class LogoutResponse implements Contract
{
    public function toResponse($request)
    {
        return redirect()->route(
            request()->routeIs('admin.*') ? 'admin.login' : 'login'
        );
    }
}