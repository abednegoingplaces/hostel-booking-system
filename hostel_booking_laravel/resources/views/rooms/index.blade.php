@extends('layouts.master')

@section('title', 'Rooms')

@section('content')
<div class="page-head">
    <div>
        <h1>Rooms</h1>
        <p class="muted">Free beds = beds in the room minus approved bookings for the semester.</p>
    </div>
</div>

<form method="GET" class="filters card">
    <div>
        <label for="hostel_id">Hostel</label>
        <select id="hostel_id" name="hostel_id">
            <option value="">All hostels</option>
            @foreach ($hostels as $h)
                <option value="{{ $h->id }}" @selected($hostelId === $h->id)>{{ $h->name }}</option>
            @endforeach
        </select>
    </div>
    @include('partials.semester-select')
    <div style="min-width:auto">
        <label><input type="checkbox" name="only_free" value="1" style="width:auto" @checked($onlyFree)> Only rooms with free beds</label>
    </div>
    <div style="min-width:auto"><button type="submit">Search</button></div>
</form>

<div class="grid">
    @forelse ($rooms as $room)
        @include('partials.room-card')
    @empty
        <p class="muted">No rooms match your search.</p>
    @endforelse
</div>
@endsection
