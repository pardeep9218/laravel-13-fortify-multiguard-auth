@extends('layouts.auth')
@section('title', 'Verify email')
@section('heading', 'Verify your email')
@section('content')
@if (session('status') === 'verification-link-sent')
    <p class="status">A new verification link has been sent to your email.</p>
@endif
<p class="note">Please click the link we emailed you to verify your address.</p>
<form method="POST" action="{{ route('verification.send') }}">
    @csrf
    <button type="submit">Resend verification email</button>
</form>
<form method="POST" action="{{ route('logout') }}">
    @csrf
    <button type="submit" style="background:transparent;color:var(--accent);border:1px solid var(--line)">Log out</button>
</form>
@endsection
