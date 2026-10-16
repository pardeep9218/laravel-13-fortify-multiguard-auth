<?php

namespace App\Http\Responses;

use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\FailedTwoFactorLoginResponse as FailedTwoFactorLoginResponseContract;

class GuardAwareFailedTwoFactorLoginResponse implements FailedTwoFactorLoginResponseContract
{
    public function toResponse($request)
    {
        [$key, $message] = $request->filled('recovery_code')
            ? ['recovery_code', __('The provided two factor recovery code was invalid.')]
            : ['code', __('The provided two factor authentication code was invalid.')];

        if ($request->wantsJson()) {
            throw ValidationException::withMessages([$key => [$message]]);
        }

        $route = $request->routeIs('admin.*') ? 'admin.two-factor.login' : 'two-factor.login';

        return redirect()->route($route)->withErrors([$key => $message]);
    }
}
