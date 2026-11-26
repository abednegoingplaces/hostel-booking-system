@extends('layouts.master')

@section('title', 'Manage hostels')

@section('content')
<div class="page-head">
    <h1>Manage hostels</h1>
    <a class="button" href="{{ route('warden.hostels.create') }}">Add hostel</a>
</div>

<div class="card table-wrap">
    <table>
        <thead><tr><th>Name</th><th>Location</th><th>Gender</th><th class="num">Rooms</th><th></th></tr></thead>
        <tbody>
        @forelse ($hostels as $hostel)
            <tr>
                <td><strong>{{ $hostel->name }}</strong><br><span class="muted">{{ $hostel->description }}</span></td>
                <td>{{ $hostel->location }}</td>
                <td>{{ ucfirst($hostel->gender) }}</td>
                <td class="num">{{ $hostel->rooms_count }}</td>
                <td>
                    <div class="actions">
                        <a class="button small light" href="{{ route('warden.rooms.index', ['hostel_id' => $hostel->id]) }}">Rooms</a>
                        <a class="button small light" href="{{ route('warden.hostels.edit', $hostel) }}">Edit</a>
                        <form method="POST" action="{{ route('warden.hostels.destroy', $hostel) }}" class="inline"
                              onsubmit="return confirm('Delete this hostel and all its rooms?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="small danger">Delete</button>
                        </form>
                    </div>
                </td>
            </tr>
        @empty
            <tr><td colspan="5" class="muted">No hostels yet. Add the first one.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
