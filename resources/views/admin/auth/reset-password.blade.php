@extends('layouts.auth')
@section('title', 'Admin reset password')
@section('heading', 'Reset password')
@section('content')
<form method="POST" action="{{ route('admin.password.update') }}">
    @csrf
    <input type="hidden" name="token" value="{{ $request->route('token') }}">

    <label for="email">Email</label>
    <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}" required>
    @error('email') <p class="error">{{ $message }}</p> @enderror

    <label for="password">New password</label>
    <input id="password" type="password" name="password" required autofocus>
    @error('password') <p class="error">{{ $message }}</p> @enderror

    <label for="password_confirmation">Confirm password</label>
    <input id="password_confirmation" type="password" name="password_confirmation" required>

    <button type="submit">Reset password</button>
</form>
@endsection
