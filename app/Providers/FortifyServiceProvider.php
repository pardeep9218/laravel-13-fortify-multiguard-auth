<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\RedirectForTwoFactorChallenge;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Http\Responses\GuardAwareFailedTwoFactorLoginResponse;
use App\Http\Responses\GuardAwarePasswordConfirmedResponse;
use App\Http\Responses\GuardAwareTwoFactorLoginResponse;
use App\Http\Responses\LogoutResponse;
use App\Http\Responses\PasswordResetResponse;
use App\Models\Admin;
use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Contracts\FailedTwoFactorLoginResponse as FailedTwoFactorLoginResponseContract;
use Laravel\Fortify\Contracts\LogoutResponse as LogoutResponseContract;
use Laravel\Fortify\Contracts\PasswordConfirmedResponse as PasswordConfirmedResponseContract;
use Laravel\Fortify\Contracts\PasswordResetResponse as PasswordResetResponseContract;
use Laravel\Fortify\Contracts\TwoFactorLoginResponse as TwoFactorLoginResponseContract;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        Fortify::ignoreRoutes(); // we register routes ourselves, once per guard

        $this->app->singleton(LogoutResponseContract::class, LogoutResponse::class);
        $this->app->singleton(PasswordResetResponseContract::class, PasswordResetResponse::class);
        $this->app->singleton(PasswordConfirmedResponseContract::class, GuardAwarePasswordConfirmedResponse::class);
        $this->app->singleton(FailedTwoFactorLoginResponseContract::class, GuardAwareFailedTwoFactorLoginResponse::class);
        $this->app->singleton(TwoFactorLoginResponseContract::class, GuardAwareTwoFactorLoginResponse::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->app->bind(StatefulGuard::class, fn () => Auth::guard(request()->routeIs('admin.*') ? 'admin' : 'web'));

        $isAdmin = fn () => request()->routeIs('admin.*');

        // Custom authentication views
        Fortify::loginView(fn () => view($isAdmin() ? 'admin.auth.login' : 'auth.login'));
        Fortify::registerView(fn () => view('auth.register'));
        Fortify::requestPasswordResetLinkView(fn () => view($isAdmin() ? 'admin.auth.forgot-password' : 'auth.forgot-password'));
        Fortify::resetPasswordView(fn (Request $request) => view($isAdmin() ? 'admin.auth.reset-password' : 'auth.reset-password', ['request' => $request]));
        Fortify::twoFactorChallengeView(fn () => view($isAdmin() ? 'admin.auth.two-factor-challenge' : 'auth.two-factor-challenge'));
        Fortify::confirmPasswordView(fn () => view($isAdmin() ? 'admin.auth.confirm-password' : 'auth.confirm-password'));
        Fortify::verifyEmailView(fn () => view('auth.verify-email'));

        Fortify::authenticateUsing(function (Request $request) {
            $admin = $request->routeIs('admin.*');
            $account = ($admin ? Admin::class : User::class)::where('email', $request->email)->first();

            if ($account && Hash::check($request->password, $account->password)) {
                if ($admin && ! $account->is_active) {
                    return null;
                }

                return $account;
            }

            return null;
        });
        // Custom authentication views

        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);
        Fortify::redirectUserForTwoFactorAuthenticationUsing(RedirectForTwoFactorChallenge::class);

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });

        RateLimiter::for('passkeys', function (Request $request) {
            $credentialId = $request->input('credential.id');

            return Limit::perMinute(10)->by(
                ($credentialId ?: $request->session()->getId()).'|'.$request->ip()
            );
        });
    }
}
