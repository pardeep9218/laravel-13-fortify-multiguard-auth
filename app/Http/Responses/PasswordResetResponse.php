<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\PasswordResetResponse as Contract;

class PasswordResetResponse implements Contract
{
    public function __construct(protected string $status) {}

    public function toResponse($request)
    {
        return redirect()
            ->route(request()->routeIs('admin.*') ? 'admin.login' : 'login')
            ->with('status', trans($this->status));
    }
}