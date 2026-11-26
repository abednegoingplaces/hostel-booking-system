@extends('layouts.master')

@section('title', 'Bookings')

@section('content')
<div class="page-head">
    <div>
        <h1>Bookings</h1>
        <p class="muted">Approving takes one free bed. A room that is already full cannot be approved.</p>
    </div>
</div>

<form method="GET" class="filters card">
    <div>
        <label for="status">Status</label>
        <select id="status" name="status">
            @foreach (\App\Models\Booking::STATUSES as $s)
                <option value="{{ $s }}" @selected($s === $status)>{{ ucfirst($s) }} ({{ $counts[$s] ?? 0 }})</option>
            @endforeach
        </select>
    </div>
    @include('partials.semester-select', ['allowAll' => true])
    <div style="min-width:auto"><button type="submit">Show</button></div>
</form>

<div class="card table-wrap">
    <table>
        <thead>
        <tr><th>Student</th><th>Hostel / room</th><th>Semester</th><th class="num">Free beds</th><th>Status</th><th>Booked on</th><th></th></tr>
        </thead>
        <tbody>
        @forelse ($bookings as $booking)
            <tr>
                <td><strong>{{ $booking->user->name }}</strong><br><span class="muted">{{ $booking->user->email }}</span></td>
                <td>{{ $booking->room->hostel->name }} · {{ $booking->room->room_number }}</td>
                <td>{{ $booking->semester }}</td>
                <td class="num">{{ $booking->free_beds }} of {{ $booking->room->capacity }}</td>
                <td><span class="badge {{ $booking->status }}">{{ ucfirst($booking->status) }}</span></td>
                <td>{{ $booking->created_at->format('j M Y') }}</td>
                <td>
                    @if ($booking->isPending())
                        <div class="actions">
                            <form method="POST" action="{{ route('warden.bookings.approve', $booking) }}" class="inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="small success">Approve</button>
                            </form>
                            <form method="POST" action="{{ route('warden.bookings.reject', $booking) }}" class="inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="small danger">Reject</button>
                            </form>
                        </div>
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="7" class="muted">No {{ $status }} bookings.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
