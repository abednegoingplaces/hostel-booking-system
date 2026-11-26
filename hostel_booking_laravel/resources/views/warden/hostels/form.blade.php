@extends('layouts.master')

@section('title', $hostel->exists ? 'Edit hostel' : 'Add hostel')

@section('content')
<div class="card form">
    <h1>{{ $hostel->exists ? 'Edit hostel' : 'Add hostel' }}</h1>

    <form method="POST" action="{{ $hostel->exists ? route('warden.hostels.update', $hostel) : route('warden.hostels.store') }}" novalidate>
        @csrf
        @if ($hostel->exists)
            @method('PUT')
        @endif

        <label for="name">Name</label>
        <input id="name" name="name" value="{{ old('name', $hostel->name) }}" required maxlength="100">
        @error('name')<p class="field-error">{{ $message }}</p>@enderror

        <label for="location">Location</label>
        <input id="location" name="location" value="{{ old('location', $hostel->location) }}" required maxlength="150">
        @error('location')<p class="field-error">{{ $message }}</p>@enderror

        <label for="gender">Gender</label>
        <select id="gender" name="gender">
            @foreach (config('hostel.genders') as $g)
                <option value="{{ $g }}" @selected(old('gender', $hostel->gender) === $g)>{{ ucfirst($g) }}</option>
            @endforeach
        </select>
        @error('gender')<p class="field-error">{{ $message }}</p>@enderror

        <label for="description">Description</label>
        <textarea id="description" name="description" maxlength="1000">{{ old('description', $hostel->description) }}</textarea>
        @error('description')<p class="field-error">{{ $message }}</p>@enderror

        <div class="form-actions">
            <button type="submit">{{ $hostel->exists ? 'Save changes' : 'Add hostel' }}</button>
            <a class="button light" href="{{ route('warden.hostels.index') }}">Cancel</a>
        </div>
    </form>
</div>
@endsection
