@extends('layouts.master')

@section('title', 'Hostels')

@section('content')
<div class="page-head">
    <div>
        <h1>Hostels</h1>
        <p class="muted">Free beds are shown for {{ $semester }}.</p>
    </div>
    <form method="GET" class="filters">
        @include('partials.semester-select')
        <div style="min-width:auto"><button type="submit">Show</button></div>
    </form>
</div>

<div class="grid">
    @forelse ($hostels as $hostel)
        <div class="card">
            <h3 style="margin-top:0">{{ $hostel->name }}</h3>
            <p class="muted">{{ $hostel->location }} · {{ ucfirst($hostel->gender) }}</p>
            <p>{{ $hostel->description }}</p>
            <p><strong>{{ $hostel->rooms->count() }}</strong> rooms · <strong>{{ $hostel->rooms->sum('free_beds') }}</strong> free beds</p>
            <a class="button small" href="{{ route('hostels.show', ['hostel' => $hostel, 'semester' => $semester]) }}">See rooms</a>
        </div>
    @empty
        <p class="muted">No hostels yet.</p>
    @endforelse
</div>
@endsection
