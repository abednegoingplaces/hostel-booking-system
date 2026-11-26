<?php

namespace App\Http\Controllers;

use App\Models\Hostel;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Public pages: list of hostels, and one hostel with its rooms. */
class HostelController extends Controller
{
    public function index(Request $request): View
    {
        $semester = $this->selectedSemester($request);
        $hostels = Hostel::with(['rooms' => fn ($q) => $q->withFreeBeds($semester)])->orderBy('name')->get();

        return view('hostels.index', compact('hostels', 'semester'));
    }

    /** Route model binding: {hostel} in the URL becomes a Hostel model (404 if missing). */
    public function show(Request $request, Hostel $hostel): View
    {
        $semester = $this->selectedSemester($request);
        $rooms = $hostel->rooms()->withFreeBeds($semester)->with('hostel')->orderBy('room_number')->get();

        return view('hostels.show', compact('hostel', 'rooms', 'semester'));
    }
}
