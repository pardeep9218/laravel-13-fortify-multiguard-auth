<?php

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Fortify\Contracts\TwoFactorAuthenticationProvider;
use Laravel\Fortify\Fortify;

uses(RefreshDatabase::class);

it('sends an admin with confirmed two-factor authentication to the admin challenge', function () {
    $admin = Admin::create([
        'name' => 'Admin User',
        'email' => 'admin@example.test',
        'password' => 'correct-password',
    ]);
    $secret = app(TwoFactorAuthenticationProvider::class)->generateSecretKey();

    $admin->forceFill([
        'two_factor_secret' => Fortify::currentEncrypter()->encrypt($secret),
        'two_factor_recovery_codes' => Fortify::currentEncrypter()->encrypt(json_encode(['recovery-code'])),
        'two_factor_confirmed_at' => now(),
    ])->save();

    $response = $this->post(route('admin.login.store'), [
        'email' => 'admin@example.test',
        'password' => 'correct-password',
    ]);

    $response->assertRedirect(route('admin.two-factor.login'));
    $this->assertGuest('admin');
    $response->assertSessionHas('login.id', $admin->id);

    $this->get(route('admin.two-factor.login'))
        ->assertOk()
        ->assertSee('Admin verification')
        ->assertSee(route('admin.two-factor.login.store'));
});

it('returns expired admin two-factor challenges to the admin login', function () {
    $this->get(route('admin.two-factor.login'))
        ->assertRedirect(route('admin.login'));
});

it('renders a scan-ready QR code and offers the manual authenticator key to admins', function () {
    $admin = Admin::create([
        'name' => 'Admin User',
        'email' => 'admin@example.test',
        'password' => 'correct-password',
    ]);
    $secret = app(TwoFactorAuthenticationProvider::class)->generateSecretKey();

    $admin->forceFill([
        'two_factor_secret' => Fortify::currentEncrypter()->encrypt($secret),
        'two_factor_recovery_codes' => Fortify::currentEncrypter()->encrypt(json_encode(['recovery-code'])),
    ])->save();

    $this->actingAs($admin, 'admin')
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('loadTwoFactorQrCode')
        ->assertSee('Can’t scan the QR code?');

    $this->getJson(route('admin.two-factor.secret-key'))
        ->assertStatus(423);

    $this->withSession(['auth.password_confirmed_at' => now()->unix()])
        ->getJson(route('admin.two-factor.qr-code'))
        ->assertOk()
        ->assertJsonStructure(['svg', 'url']);

    $this->withSession(['auth.password_confirmed_at' => now()->unix()])
        ->get(route('admin.two-factor.secret-key'))
        ->assertOk()
        ->assertJsonPath('secretKey', $secret);
});

it('returns admins to their dashboard after confirming their password', function () {
    $admin = Admin::create([
        'name' => 'Admin User',
        'email' => 'admin@example.test',
        'password' => 'correct-password',
    ]);

    $this->actingAs($admin, 'admin')
        ->post(route('admin.password.confirm.store'), ['password' => 'correct-password'])
        ->assertRedirect(route('admin.dashboard'));
});
