@php
    $account = auth($guardName)->user();
    $routePrefix = $guardName === 'admin' ? 'admin.' : '';
@endphp

@if (\Laravel\Fortify\Features::enabled(\Laravel\Fortify\Features::twoFactorAuthentication()))
<section id="two-factor-panel-{{ $guardName }}" class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm" data-recovery-codes-url="{{ route($routePrefix.'two-factor.recovery-codes') }}" data-secret-key-url="{{ route($routePrefix.'two-factor.secret-key') }}" data-qr-code-url="{{ route($routePrefix.'two-factor.qr-code') }}" data-password-confirm-url="{{ route($routePrefix.'password.confirm') }}">
    <h2 class="text-lg font-semibold text-gray-900">Two-factor authentication</h2>
    <p class="mt-2 text-sm text-gray-500">Add an authenticator app as an extra sign-in check.</p>

    @if (! $account->two_factor_secret)
        <form method="POST" action="{{ route($routePrefix.'two-factor.enable') }}">
            @csrf
            <button class="mt-4 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white" type="submit">
                Enable two-factor authentication
            </button>
        </form>
    @elseif (! $account->two_factor_confirmed_at)
        <p class="mt-4 text-sm text-gray-700">Scan this QR code with your authenticator app, then enter its current code to confirm setup.</p>
        <button class="mt-4 rounded-lg border border-gray-300 px-4 py-2 text-sm" type="button" onclick="loadTwoFactorQrCode('{{ $guardName }}')">Show setup QR code</button>
        <div class="my-4 hidden rounded bg-white p-6" id="two-factor-qr-container-{{ $guardName }}">
            <img id="two-factor-qr-{{ $guardName }}" alt="Authenticator app setup QR code" width="256" height="256" class="block h-64 w-64">
        </div>
        <details class="mt-3 text-sm text-gray-600">
            <summary class="cursor-pointer font-medium">Can’t scan the QR code?</summary>
            <p class="mt-2">Enter this setup key manually in your authenticator app:</p>
            <button class="mt-2 rounded border border-gray-300 px-3 py-2" type="button" onclick="showTwoFactorSecretKey('{{ $guardName }}')">Show setup key</button>
            <code id="two-factor-secret-key-{{ $guardName }}" class="mt-2 block break-all"></code>
        </details>
        <form method="POST" action="{{ route($routePrefix.'two-factor.confirm') }}" class="mt-4">
            @csrf
            <label class="block text-sm font-medium text-gray-700" for="two-factor-code-{{ $guardName }}">Authentication code</label>
            <input class="mt-1 rounded border border-gray-300 px-3 py-2" id="two-factor-code-{{ $guardName }}" type="text" name="code" inputmode="numeric" autocomplete="one-time-code" required>
            @error('code') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            <button class="ml-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white" type="submit">Confirm setup</button>
        </form>
    @else
        <p class="mt-4 text-sm font-medium text-green-700">Two-factor authentication is enabled.</p>
        <div class="mt-4 flex flex-wrap gap-3">
            <button class="rounded-lg border border-gray-300 px-4 py-2 text-sm" type="button" onclick="showRecoveryCodes('{{ $guardName }}')">Show recovery codes</button>
            <form method="POST" action="{{ route($routePrefix.'two-factor.regenerate-recovery-codes') }}">
                @csrf
                <button class="rounded-lg border border-gray-300 px-4 py-2 text-sm" type="submit">Generate new recovery codes</button>
            </form>
            <form method="POST" action="{{ route($routePrefix.'two-factor.disable') }}">
                @csrf
                @method('DELETE')
                <button class="rounded-lg border border-red-300 px-4 py-2 text-sm text-red-700" type="submit">Disable two-factor authentication</button>
            </form>
        </div>
        <pre id="recovery-codes-{{ $guardName }}" class="mt-4 hidden whitespace-pre-wrap rounded bg-gray-50 p-4 text-sm"></pre>
    @endif

    <script>
        function redirectForPasswordConfirmation(panel) {
            window.location.assign(panel.dataset.passwordConfirmUrl);
        }

        async function loadTwoFactorQrCode(guard) {
            const panel = document.getElementById(`two-factor-panel-${guard}`);
            const response = await fetch(panel.dataset.qrCodeUrl, {
                headers: { 'Accept': 'application/json' },
                credentials: 'same-origin',
            });

            if (response.status === 423) {
                redirectForPasswordConfirmation(panel);
                return;
            }

            if (response.ok) {
                const data = await response.json();
                document.getElementById(`two-factor-qr-${guard}`).src = `data:image/svg+xml;charset=utf-8,${encodeURIComponent(data.svg)}`;
                const qrContainer = document.getElementById(`two-factor-qr-container-${guard}`);
                qrContainer.classList.remove('hidden');
                qrContainer.classList.add('inline-flex');
            }
        }

        async function showTwoFactorSecretKey(guard) {
            const panel = document.getElementById(`two-factor-panel-${guard}`);
            const response = await fetch(panel.dataset.secretKeyUrl, {
                headers: { 'Accept': 'application/json' },
                credentials: 'same-origin',
            });

            if (response.status === 423) {
                redirectForPasswordConfirmation(panel);
                return;
            }

            if (response.ok) {
                const data = await response.json();
                document.getElementById(`two-factor-secret-key-${guard}`).textContent = data.secretKey;
            }
        }

        async function showRecoveryCodes(guard) {
            const panel = document.getElementById(`two-factor-panel-${guard}`);
            const response = await fetch(panel.dataset.recoveryCodesUrl, {
                headers: { 'Accept': 'application/json' },
                credentials: 'same-origin',
            });
            const output = document.getElementById(`recovery-codes-${guard}`);

            if (response.status === 423) {
                redirectForPasswordConfirmation(panel);
                return;
            }

            if (response.ok) {
                output.textContent = (await response.json()).join('\n');
                output.classList.remove('hidden');
            }
        }
    </script>
</section>
@endif