@extends('layouts.auth')
@section('title', 'Admin dashboard')
@section('heading', 'Admin dashboard')
@section('content')
<p class="note">Signed in as {{ auth('admin')->user()->name }} ({{ auth('admin')->user()->email }})</p>
<!-- @include('components.two-factor-management', ['guardName' => 'admin']) -->
<form method="POST" action="{{ route('admin.logout') }}">
    @csrf
    <button type="submit">Log out</button>
</form>
@endsection
