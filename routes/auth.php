<?php

use App\Http\Controllers\AdminTwoFactorChallengeController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;
use Laravel\Fortify\Http\Controllers\ConfirmablePasswordController;
use Laravel\Fortify\Http\Controllers\ConfirmedTwoFactorAuthenticationController;
use Laravel\Fortify\Http\Controllers\EmailVerificationNotificationController;
use Laravel\Fortify\Http\Controllers\EmailVerificationPromptController;
use Laravel\Fortify\Http\Controllers\NewPasswordController;
use Laravel\Fortify\Http\Controllers\PasswordResetLinkController;
use Laravel\Fortify\Http\Controllers\RecoveryCodeController;
use Laravel\Fortify\Http\Controllers\RegisteredUserController;
use Laravel\Fortify\Http\Controllers\TwoFactorAuthenticatedSessionController;
use Laravel\Fortify\Http\Controllers\TwoFactorAuthenticationController;
use Laravel\Fortify\Http\Controllers\TwoFactorQrCodeController;
use Laravel\Fortify\Http\Controllers\TwoFactorSecretKeyController;
use Laravel\Fortify\Http\Controllers\VerifyEmailController;

foreach (['web' => '', 'admin' => 'admin'] as $guard => $prefix) {
    Route::middleware("fortify.context:$guard")
        ->prefix($prefix)
        ->name($guard === 'admin' ? 'admin.' : '')
        ->group(function () use ($guard) {

            Route::middleware("guest:$guard")->group(function () use ($guard) {
                Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
                Route::post('login', [AuthenticatedSessionController::class, 'store'])
                    ->middleware('throttle:login')->name('login.store');

                Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
                Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');
                Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
                Route::post('reset-password', [NewPasswordController::class, 'store'])->name('password.update');

                if ($guard === 'web') {
                    Route::get('register', [RegisteredUserController::class, 'create'])->name('register');
                    Route::post('register', [RegisteredUserController::class, 'store']);
                }
            });

            Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
                ->middleware("auth:$guard")->name('logout');

            if (Features::enabled(Features::twoFactorAuthentication())) {
                Route::middleware("guest:$guard")->group(function () use ($guard) {
                    Route::get('two-factor-challenge', $guard === 'admin'
                        ? [AdminTwoFactorChallengeController::class, 'create']
                        : [TwoFactorAuthenticatedSessionController::class, 'create'])
                        ->name('two-factor.login');
                    Route::post('two-factor-challenge', [TwoFactorAuthenticatedSessionController::class, 'store'])
                        ->middleware('throttle:two-factor')->name('two-factor.login.store');
                });

                Route::middleware("auth:$guard")->group(function () use ($guard) {
                    $confirmationRoute = $guard === 'admin' ? 'admin.password.confirm' : 'password.confirm';
                    $twoFactorMiddleware = ['password.confirm:'.$confirmationRoute];

                    Route::get('user/confirm-password', [ConfirmablePasswordController::class, 'show'])
                        ->name('password.confirm');
                    Route::post('user/confirm-password', [ConfirmablePasswordController::class, 'store'])
                        ->name('password.confirm.store');

                    Route::post('user/two-factor-authentication', [TwoFactorAuthenticationController::class, 'store'])
                        ->middleware($twoFactorMiddleware)->name('two-factor.enable');
                    Route::post('user/confirmed-two-factor-authentication', [ConfirmedTwoFactorAuthenticationController::class, 'store'])
                        ->middleware($twoFactorMiddleware)->name('two-factor.confirm');
                    Route::delete('user/two-factor-authentication', [TwoFactorAuthenticationController::class, 'destroy'])
                        ->middleware($twoFactorMiddleware)->name('two-factor.disable');
                    Route::get('user/two-factor-qr-code', [TwoFactorQrCodeController::class, 'show'])
                        ->middleware($twoFactorMiddleware)->name('two-factor.qr-code');
                    Route::get('user/two-factor-secret-key', [TwoFactorSecretKeyController::class, 'show'])
                        ->middleware($twoFactorMiddleware)->name('two-factor.secret-key');
                    Route::get('user/two-factor-recovery-codes', [RecoveryCodeController::class, 'index'])
                        ->middleware($twoFactorMiddleware)->name('two-factor.recovery-codes');
                    Route::post('user/two-factor-recovery-codes', [RecoveryCodeController::class, 'store'])
                        ->middleware($twoFactorMiddleware)->name('two-factor.regenerate-recovery-codes');
                });
            }

            if ($guard === 'web') {
                Route::middleware('auth:web')->group(function () {
                    Route::get('email/verify', EmailVerificationPromptController::class)->name('verification.notice');
                    Route::get('email/verify/{id}/{hash}', VerifyEmailController::class)
                        ->middleware(['signed', 'throttle:6,1'])->name('verification.verify');
                    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
                        ->middleware('throttle:6,1')->name('verification.send');
                });
            }
        });
}
