{{-- One room as a card, with a Book button for students. Needs $room (with free_beds) and $semester. --}}
<div class="card room-card">
    @if ($room->photo_url)
        <img src="{{ $room->photo_url }}" alt="Room {{ $room->room_number }}">
    @else
        <div class="no-photo">No photo yet</div>
    @endif
    <h3>Room {{ $room->room_number }}</h3>
    <p class="muted">{{ $room->hostel->name }} · {{ ucfirst($room->hostel->gender) }}</p>
    <p>
        KSh {{ number_format($room->price_per_semester) }} per semester<br>
        {{ $room->capacity }} beds ·
        @if ($room->free_beds > 0)
            <span class="badge free">{{ $room->free_beds }} free</span>
        @else
            <span class="badge full">Full</span>
        @endif
    </p>

    @if ($room->free_beds > 0)
        @auth
            @if (auth()->user()->isStudent())
                <form method="POST" action="{{ route('bookings.store') }}">
                    @csrf
                    <input type="hidden" name="room_id" value="{{ $room->id }}">
                    <input type="hidden" name="semester" value="{{ $semester }}">
                    <button type="submit" class="small">Book for {{ $semester }}</button>
                </form>
            @endif
        @else
            <a class="button small light" href="{{ route('login') }}">Log in to book</a>
        @endauth
    @endif
</div>
