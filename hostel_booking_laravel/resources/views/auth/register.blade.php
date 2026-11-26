@extends('layouts.master')

@section('title', 'Register')

@section('content')
<div class="card narrow">
    <h1>Create a student account</h1>

    <form method="POST" action="{{ route('register') }}" novalidate>
        @csrf

        <label for="name">Full name</label>
        <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name" maxlength="255">
        @error('name')<p class="field-error">{{ $message }}</p>@enderror

        <label for="email">Email</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username">
        @error('email')<p class="field-error">{{ $message }}</p>@enderror

        <label for="password">Password</label>
        <input id="password" type="password" name="password" required autocomplete="new-password">
        @error('password')<p class="field-error">{{ $message }}</p>@enderror

        <label for="password_confirmation">Confirm password</label>
        <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password">
        @error('password_confirmation')<p class="field-error">{{ $message }}</p>@enderror

        <div class="form-actions"><button type="submit">Register</button></div>
    </form>

    <p class="muted">Already registered? <a href="{{ route('login') }}">Log in</a>.</p>
</div>
@endsection
