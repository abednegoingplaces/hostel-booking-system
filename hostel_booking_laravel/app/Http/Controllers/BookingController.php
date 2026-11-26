<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookingRequest;
use App\Models\Booking;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/** Student side: my bookings, book a room, cancel a pending booking. */
class BookingController extends Controller
{
    public function index(Request $request): View
    {
        $bookings = $request->user()->bookings()->with('room.hostel')->latest()->latest('id')->get();

        return view('bookings.index', compact('bookings'));
    }

    public function store(StoreBookingRequest $request): RedirectResponse
    {
        $booking = $request->user()->bookings()->create($request->validated()); // status defaults to pending
        $booking->load('room.hostel');

        return redirect()->route('bookings.index')->with('success',
            "Booking sent for room {$booking->room->room_number} ({$booking->room->hostel->name}), {$booking->semester}. The warden will review it.");
    }

    public function cancel(Booking $booking): RedirectResponse
    {
        Gate::authorize('cancel', $booking); // BookingPolicy: own booking and still pending

        $booking->status = 'cancelled';
        $booking->save();

        return redirect()->route('bookings.index')->with('success', 'Booking cancelled.');
    }
}
