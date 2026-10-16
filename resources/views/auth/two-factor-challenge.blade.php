@extends('layouts.auth')
@section('title', 'Two-factor authentication')
@section('heading', 'Two-factor authentication')
@section('content')
<p class="note">Enter the authentication code from your app, or use a recovery code.</p>
<form method="POST" action="{{ route('two-factor.login.store') }}">
    @csrf
    <label for="code">Authentication code</label>
    <input id="code" type="text" name="code" inputmode="numeric" autocomplete="one-time-code" autofocus>
    @error('code') <p class="error">{{ $message }}</p> @enderror

    <label for="recovery_code">Recovery code</label>
    <input id="recovery_code" type="text" name="recovery_code" autocomplete="off">
    @error('recovery_code') <p class="error">{{ $message }}</p> @enderror

    <button type="submit">Verify and log in</button>
</form>
@endsection