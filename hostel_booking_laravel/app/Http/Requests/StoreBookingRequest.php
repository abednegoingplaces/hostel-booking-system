<?php

namespace App\Http\Requests;

use App\Models\Room;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/** A student books a room: the room must have a free bed and the student no other active booking. */
class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isStudent() ?? false;
    }

    public function rules(): array
    {
        return [
            'room_id' => ['required', 'integer', 'exists:rooms,id'],
            'semester' => ['required', Rule::in(config('hostel.semesters'))],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }
                $semester = $this->input('semester');
                $room = Room::withFreeBeds($semester)->find($this->input('room_id'));

                if ($room->free_beds < 1) {
                    $validator->errors()->add('room_id', "Room {$room->room_number} is full for {$semester}.");
                }

                $hasActive = $this->user()->bookings()
                    ->where('semester', $semester)
                    ->whereIn('status', ['pending', 'approved'])
                    ->exists();
                if ($hasActive) {
                    $validator->errors()->add('semester', "You already have a pending or approved booking for {$semester}. Cancel it first to choose another room.");
                }
            },
        ];
    }
}
