@extends('layouts.master')

@section('title', 'Home')

@section('content')
<section class="hero">
    <h1>Find and book your hostel room</h1>
    <p>See which rooms still have free beds, book one for the semester online, and follow your booking
       until the warden approves it. No queues, no paper forms.</p>
    <p>
        <a class="button light" href="{{ route('rooms.index') }}">Browse rooms</a>
        @guest
            <a class="button" href="{{ route('register') }}" style="background:#fff;color:#0f2a4a">Create a student account</a>
        @endguest
    </p>
</section>

<div class="stats">
    <div class="card stat"><strong>{{ $hostelCount }}</strong><span class="muted">Hostels</span></div>
    <div class="card stat"><strong>{{ $roomCount }}</strong><span class="muted">Rooms</span></div>
    <div class="card stat"><strong>{{ $freeBeds }}</strong><span class="muted">Free beds for {{ $semester }}</span></div>
</div>

<h2>How booking works</h2>
<ol class="card">
    <li>Log in and pick a room that still has free beds.</li>
    <li>Your booking is saved as <span class="badge pending">pending</span>.</li>
    <li>The warden approves it (<span class="badge approved">approved</span>, one bed fewer) or rejects it.</li>
    <li>You see the new status on your bookings page, and you can cancel while it is still pending.</li>
</ol>
@endsection
