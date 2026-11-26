@extends('layouts.master')

@section('title', $hostel->name)

@section('content')
<div class="page-head">
    <div>
        <h1>{{ $hostel->name }}</h1>
        <p class="muted">{{ $hostel->location }} · {{ ucfirst($hostel->gender) }} · free beds for {{ $semester }}</p>
        <p>{{ $hostel->description }}</p>
    </div>
    <form method="GET" class="filters">
        @include('partials.semester-select')
        <div style="min-width:auto"><button type="submit">Show</button></div>
    </form>
</div>

<div class="grid">
    @forelse ($rooms as $room)
        @include('partials.room-card')
    @empty
        <p class="muted">This hostel has no rooms yet.</p>
    @endforelse
</div>
@endsection
