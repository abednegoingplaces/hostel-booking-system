@extends('layouts.master')

@section('title', $room->exists ? 'Edit room' : 'Add room')

@section('content')
<div class="card form">
    <h1>{{ $room->exists ? 'Edit room' : 'Add room' }}</h1>

    <form method="POST" action="{{ $room->exists ? route('warden.rooms.update', $room) : route('warden.rooms.store') }}"
          enctype="multipart/form-data" novalidate>
        @csrf
        @if ($room->exists)
            @method('PUT')
        @endif

        <label for="hostel_id">Hostel</label>
        <select id="hostel_id" name="hostel_id">
            <option value="">Choose a hostel</option>
            @foreach ($hostels as $h)
                <option value="{{ $h->id }}" @selected((int) old('hostel_id', $room->hostel_id) === $h->id)>{{ $h->name }}</option>
            @endforeach
        </select>
        @error('hostel_id')<p class="field-error">{{ $message }}</p>@enderror

        <label for="room_number">Room number</label>
        <input id="room_number" name="room_number" value="{{ old('room_number', $room->room_number) }}" required maxlength="20">
        @error('room_number')<p class="field-error">{{ $message }}</p>@enderror

        <label for="capacity">Beds (capacity)</label>
        <input type="number" id="capacity" name="capacity" value="{{ old('capacity', $room->capacity) }}" min="1" max="12" required>
        @error('capacity')<p class="field-error">{{ $message }}</p>@enderror

        <label for="price_per_semester">Price per semester (KSh)</label>
        <input type="number" id="price_per_semester" name="price_per_semester" value="{{ old('price_per_semester', $room->price_per_semester) }}" min="0" step="50" required>
        @error('price_per_semester')<p class="field-error">{{ $message }}</p>@enderror

        <label for="photo">Photo (JPG, PNG or WEBP, up to 2 MB)</label>
        @if ($room->photo_url)
            <img src="{{ $room->photo_url }}" alt="Current photo" style="max-width:220px;border-radius:8px;display:block;margin-bottom:8px">
        @endif
        <input type="file" id="photo" name="photo" accept="image/jpeg,image/png,image/webp">
        @error('photo')<p class="field-error">{{ $message }}</p>@enderror

        <div class="form-actions">
            <button type="submit">{{ $room->exists ? 'Save changes' : 'Add room' }}</button>
            <a class="button light" href="{{ route('warden.rooms.index') }}">Cancel</a>
        </div>
    </form>
</div>
@endsection
