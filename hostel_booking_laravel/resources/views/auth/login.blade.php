@extends('layouts.master')

@section('title', 'Log in')

@section('content')
<div class="card narrow">
    <h1>Log in</h1>

    @if (session('status'))
        <p class="flash info">{{ session('status') }}</p>
    @endif

    <form method="POST" action="{{ route('login') }}" novalidate>
        @csrf

        <label for="email">Email</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username">
        @error('email')<p class="field-error">{{ $message }}</p>@enderror

        <label for="password">Password</label>
        <input id="password" type="password" name="password" required autocomplete="current-password">
        @error('password')<p class="field-error">{{ $message }}</p>@enderror

        <label style="font-weight:400"><input type="checkbox" name="remember" style="width:auto"> Remember me</label>

        <div class="form-actions"><button type="submit">Log in</button></div>
    </form>

    <p class="muted">
        No account? <a href="{{ route('register') }}">Register as a student</a>.
        @if (Route::has('password.request'))
            <br><a href="{{ route('password.request') }}">Forgot your password?</a>
        @endif
    </p>
</div>
@endsection
