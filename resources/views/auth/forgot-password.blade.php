@extends('layouts.auth')
@section('title', 'Forgot password')
@section('heading', 'Forgot password')
@section('content')
<p class="note">Enter your email and we'll send you a reset link.</p>
<form method="POST" action="{{ route('password.email') }}">
    @csrf
    <label for="email">Email</label>
    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus>
    @error('email') <p class="error">{{ $message }}</p> @enderror
    <button type="submit">Send reset link</button>
</form>
<div class="links"><a href="{{ route('login') }}">Back to login</a></div>
@endsection
