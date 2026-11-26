<?php

namespace App\Http\Controllers\Warden;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Room;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use RuntimeException;

/** Warden: see bookings, approve or reject pending ones. */
class BookingController extends Controller
{
    public function index(Request $request): View
    {
        $status = in_array($request->query('status'), Booking::STATUSES, true) ? $request->query('status') : 'pending';
        $semester = in_array($request->query('semester'), config('hostel.semesters'), true) ? $request->query('semester') : null;

        $bookings = Booking::with(['user', 'room.hostel'])
            ->where('status', $status)
            ->when($semester, fn ($q) => $q->where('semester', $semester))
            ->oldest()->oldest('id')
            ->get();

        // Free beds for each booking's room in that booking's semester.
        foreach ($bookings as $booking) {
            $booking->free_beds = max(0, $booking->room->capacity - $booking->room->approvedCount($booking->semester));
        }

        $counts = Booking::selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status');

        return view('warden.bookings.index', compact('bookings', 'status', 'semester', 'counts'));
    }

    /**
     * Approve inside a transaction that locks the room row and re-counts approved bookings,
     * so two approvals at the same moment can never overfill a room.
     */
    public function approve(Booking $booking): RedirectResponse
    {
        try {
            $roomNumber = DB::transaction(function () use ($booking) {
                $booking = Booking::lockForUpdate()->findOrFail($booking->id);
                if (! $booking->isPending()) {
                    throw new RuntimeException('Only pending bookings can be approved.');
                }

                $room = Room::lockForUpdate()->findOrFail($booking->room_id);
                if ($room->approvedCount($booking->semester) >= $room->capacity) {
                    throw new RuntimeException("Room {$room->room_number} is already full for {$booking->semester}. Reject this booking instead.");
                }

                $alreadyHoused = Booking::where('user_id', $booking->user_id)
                    ->where('semester', $booking->semester)->where('status', 'approved')->exists();
                if ($alreadyHoused) {
                    throw new RuntimeException("This student already has an approved room for {$booking->semester}.");
                }

                $booking->status = 'approved';
                $booking->save();

                return $room->room_number;
            });
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', "Booking approved. Room {$roomNumber} now has one bed fewer.");
    }

    public function reject(Booking $booking): RedirectResponse
    {
        if (! $booking->isPending()) {
            return back()->with('error', 'Only pending bookings can be rejected.');
        }
        $booking->status = 'rejected';
        $booking->save();

        return back()->with('success', 'Booking rejected.');
    }
}
