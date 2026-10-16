@extends('layouts.auth')
@section('title', 'Register')
@section('heading', 'Create account')
@section('content')
<form method="POST" action="{{ route('register') }}">
    @csrf
    <label for="name">Name</label>
    <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus>
    @error('name') <p class="error">{{ $message }}</p> @enderror

    <label for="email">Email</label>
    <input id="email" type="email" name="email" value="{{ old('email') }}" required>
    @error('email') <p class="error">{{ $message }}</p> @enderror

    <label for="password">Password</label>
    <input id="password" type="password" name="password" required>
    @error('password') <p class="error">{{ $message }}</p> @enderror

    <label for="password_confirmation">Confirm password</label>
    <input id="password_confirmation" type="password" name="password_confirmation" required>

    <button type="submit">Register</button>
</form>
<div class="links"><a href="{{ route('login') }}">Already have an account?</a></div>
@endsection
