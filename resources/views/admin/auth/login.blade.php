@extends('layouts.auth')
@section('title', 'Admin login')
@section('heading', 'Admin login')
@section('content')
<form method="POST" action="{{ route('admin.login') }}">
    @csrf
    <label for="email">Email</label>
    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus>
    @error('email') <p class="error">{{ $message }}</p> @enderror

    <label for="password">Password</label>
    <input id="password" type="password" name="password" required>
    @error('password') <p class="error">{{ $message }}</p> @enderror

    <label class="remember"><input type="checkbox" name="remember"> Remember me</label>
    <button type="submit">Log in</button>
</form>
<div class="links"><a href="{{ route('admin.password.request') }}">Forgot password?</a></div>
@endsection
