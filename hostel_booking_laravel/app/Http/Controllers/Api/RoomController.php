<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\RoomResource;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * GET /api/rooms                          -> every room, default semester
 * GET /api/rooms?semester=Jan-Apr%202027  -> a chosen semester
 * GET /api/rooms?hostel_id=1              -> one hostel only
 */
class RoomController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'semester' => ['nullable', Rule::in(config('hostel.semesters'))],
            'hostel_id' => ['nullable', 'integer'],
        ]);
        $semester = $validated['semester'] ?? config('hostel.default_semester');

        $rooms = Room::withFreeBeds($semester)
            ->with('hostel')
            ->when($validated['hostel_id'] ?? null, fn ($q, $id) => $q->where('hostel_id', $id))
            ->orderBy('hostel_id')->orderBy('room_number')
            ->get();

        return RoomResource::collection($rooms)->additional([
            'semester' => $semester,
            'count' => $rooms->count(),
        ]);
    }
}
