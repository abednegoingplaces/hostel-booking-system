@extends('layouts.master')

@section('title', 'My bookings')

@section('content')
<div class="page-head">
    <div>
        <h1>My bookings</h1>
        <p class="muted">You can cancel a booking while it is still pending.</p>
    </div>
    <a class="button" href="{{ route('rooms.index') }}">Book a room</a>
</div>

<div class="card table-wrap">
    <table>
        <thead>
        <tr><th>Hostel</th><th>Room</th><th>Semester</th><th class="num">Price</th><th>Status</th><th>Booked on</th><th></th></tr>
        </thead>
        <tbody>
        @forelse ($bookings as $booking)
            <tr>
                <td>{{ $booking->room->hostel->name }}</td>
                <td>{{ $booking->room->room_number }}</td>
                <td>{{ $booking->semester }}</td>
                <td class="num">KSh {{ number_format($booking->room->price_per_semester) }}</td>
                <td><span class="badge {{ $booking->status }}">{{ ucfirst($booking->status) }}</span></td>
                <td>{{ $booking->created_at->format('j M Y') }}</td>
                <td>
                    @can('cancel', $booking)
                        <form method="POST" action="{{ route('bookings.cancel', $booking) }}" class="inline" onsubmit="return confirm('Cancel this booking?')">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="small light">Cancel</button>
                        </form>
                    @endcan
                </td>
            </tr>
        @empty
            <tr><td colspan="7" class="muted">You have no bookings yet.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
