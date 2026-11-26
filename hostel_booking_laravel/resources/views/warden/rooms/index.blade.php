@extends('layouts.master')

@section('title', 'Manage rooms')

@section('content')
<div class="page-head">
    <h1>Manage rooms</h1>
    <a class="button" href="{{ route('warden.rooms.create', $hostelId ? ['hostel_id' => $hostelId] : []) }}">Add room</a>
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
    <div style="min-width:auto"><button type="submit">Show</button></div>
</form>

<div class="card table-wrap">
    <table>
        <thead>
        <tr><th>Hostel</th><th>Room</th><th class="num">Beds</th><th class="num">Free</th><th class="num">Price</th><th>In the room ({{ $semester }})</th><th></th></tr>
        </thead>
        <tbody>
        @forelse ($rooms as $room)
            <tr>
                <td>{{ $room->hostel->name }}</td>
                <td><strong>{{ $room->room_number }}</strong>@if ($room->photo_path) <span class="muted">· photo</span>@endif</td>
                <td class="num">{{ $room->capacity }}</td>
                <td class="num"><span class="badge {{ $room->free_beds > 0 ? 'free' : 'full' }}">{{ $room->free_beds }}</span></td>
                <td class="num">KSh {{ number_format($room->price_per_semester) }}</td>
                <td>
                    @if ($room->bookings->isNotEmpty())
                        {{ $room->bookings->pluck('user.name')->sort()->join(', ') }}
                    @else
                        <span class="muted">Nobody yet</span>
                    @endif
                </td>
                <td>
                    <div class="actions">
                        <a class="button small light" href="{{ route('warden.rooms.edit', $room) }}">Edit</a>
                        <form method="POST" action="{{ route('warden.rooms.destroy', $room) }}" class="inline" onsubmit="return confirm('Delete this room?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="small danger">Delete</button>
                        </form>
                    </div>
                </td>
            </tr>
        @empty
            <tr><td colspan="7" class="muted">No rooms yet.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
