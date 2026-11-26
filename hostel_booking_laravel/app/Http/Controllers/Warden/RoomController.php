<?php

namespace App\Http\Controllers\Warden;

use App\Http\Controllers\Controller;
use App\Http\Requests\RoomRequest;
use App\Models\Hostel;
use App\Models\Room;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/** Warden: full CRUD on rooms, with a photo upload (resource controller). */
class RoomController extends Controller
{
    public function index(Request $request): View
    {
        $semester = $this->selectedSemester($request);
        $hostelId = $request->integer('hostel_id') ?: null;

        $rooms = Room::withFreeBeds($semester)
            ->with(['hostel', 'bookings' => fn ($q) => $q->where('semester', $semester)->where('status', 'approved')->with('user')])
            ->when($hostelId, fn ($q) => $q->where('hostel_id', $hostelId))
            ->get()
            ->sortBy([['hostel.name', 'asc'], ['room_number', 'asc']])
            ->values();

        return view('warden.rooms.index', [
            'rooms' => $rooms,
            'hostels' => Hostel::orderBy('name')->get(['id', 'name']),
            'semester' => $semester,
            'hostelId' => $hostelId,
        ]);
    }

    public function create(Request $request): View|RedirectResponse
    {
        if (Hostel::doesntExist()) {
            return redirect()->route('warden.hostels.create')->with('info', 'Add a hostel before adding rooms.');
        }

        return view('warden.rooms.form', [
            'room' => new Room(['hostel_id' => $request->integer('hostel_id') ?: null]),
            'hostels' => Hostel::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(RoomRequest $request): RedirectResponse
    {
        $data = $request->safe()->except('photo');
        if ($request->hasFile('photo')) {
            $data['photo_path'] = $request->file('photo')->store('rooms', 'public'); // random file name
        }
        $room = Room::create($data);

        return redirect()->route('warden.rooms.index', ['hostel_id' => $room->hostel_id])->with('success', 'Room added.');
    }

    public function edit(Room $room): View
    {
        return view('warden.rooms.form', [
            'room' => $room,
            'hostels' => Hostel::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(RoomRequest $request, Room $room): RedirectResponse
    {
        $data = $request->safe()->except('photo');
        if ($request->hasFile('photo')) {
            if ($room->photo_path) {
                Storage::disk('public')->delete($room->photo_path); // replace the old photo
            }
            $data['photo_path'] = $request->file('photo')->store('rooms', 'public');
        }
        $room->update($data);

        return redirect()->route('warden.rooms.index', ['hostel_id' => $room->hostel_id])->with('success', 'Room updated.');
    }

    public function destroy(Room $room): RedirectResponse
    {
        if ($room->bookings()->whereIn('status', ['pending', 'approved'])->exists()) {
            return back()->with('error', 'This room has pending or approved bookings, so it cannot be deleted.');
        }
        if ($room->photo_path) {
            Storage::disk('public')->delete($room->photo_path);
        }
        $room->delete();

        return redirect()->route('warden.rooms.index')->with('success', 'Room deleted.');
    }
}
