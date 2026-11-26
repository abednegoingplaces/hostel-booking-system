<?php

namespace App\Http\Controllers;

use App\Models\Hostel;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Public room search: filter by hostel and semester, see free beds. */
class RoomController extends Controller
{
    public function index(Request $request): View
    {
        $semester = $this->selectedSemester($request);
        $hostelId = $request->integer('hostel_id') ?: null;
        $onlyFree = $request->boolean('only_free');

        $rooms = Room::withFreeBeds($semester)
            ->with('hostel')
            ->when($hostelId, fn ($q) => $q->where('hostel_id', $hostelId))
            ->get()
            ->sortBy([['hostel.name', 'asc'], ['room_number', 'asc']])
            ->values()
            ->when($onlyFree, fn ($rooms) => $rooms->filter(fn ($r) => $r->free_beds > 0));

        return view('rooms.index', [
            'rooms' => $rooms,
            'hostels' => Hostel::orderBy('name')->get(['id', 'name']),
            'semester' => $semester,
            'hostelId' => $hostelId,
            'onlyFree' => $onlyFree,
        ]);
    }
}
