@extends('layouts.auth')
@section('title', 'Confirm admin password')
@section('heading', 'Confirm your password')
@section('content')
<p class="note">For your security, confirm your password before changing two-factor settings.</p>
<form method="POST" action="{{ route('admin.password.confirm.store') }}">
    @csrf
    <label for="password">Password</label>
    <input id="password" type="password" name="password" required autofocus autocomplete="current-password">
    @error('password') <p class="error">{{ $message }}</p> @enderror
    <button type="submit">Confirm password</button>
</form>
@endsection