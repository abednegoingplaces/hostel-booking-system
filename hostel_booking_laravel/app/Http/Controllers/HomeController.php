<?php

namespace App\Http\Controllers;

use App\Models\Hostel;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $semester = config('hostel.default_semester');
        $rooms = Room::withFreeBeds($semester)->get();

        return view('home', [
            'semester' => $semester,
            'hostelCount' => Hostel::count(),
            'roomCount' => $rooms->count(),
            'freeBeds' => $rooms->sum('free_beds'),
        ]);
    }

    /** After login, send each role to its main page. */
    public function dashboard(Request $request)
    {
        return $request->user()->isWarden()
            ? redirect()->route('warden.bookings.index')
            : redirect()->route('rooms.index');
    }
}
