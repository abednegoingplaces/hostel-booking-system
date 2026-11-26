<?php

namespace App\Http\Requests;

use App\Models\Room;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/** Validation for adding and editing a room, including the photo upload. */
class RoomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isWarden() ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['room_number' => strtoupper(trim((string) $this->input('room_number')))]);
    }

    public function rules(): array
    {
        return [
            'hostel_id' => ['required', 'integer', 'exists:hostels,id'],
            'room_number' => [
                'required', 'string', 'max:20', 'regex:/^[A-Z0-9-]+$/',
                // Unique within the chosen hostel, ignoring this room when editing.
                Rule::unique('rooms')->where('hostel_id', $this->input('hostel_id'))->ignore($this->route('room')),
            ],
            'capacity' => ['required', 'integer', 'min:1', 'max:12'],
            'price_per_semester' => ['required', 'numeric', 'min:0', 'max:1000000'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'], // max 2 MB
        ];
    }

    public function messages(): array
    {
        return [
            'room_number.unique' => 'That hostel already has a room with this number.',
            'room_number.regex' => 'Use only letters, numbers and dashes.',
        ];
    }

    /** Do not let a room shrink below the number of students already approved in it. */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                $room = $this->route('room');
                if (! $room instanceof Room || $validator->errors()->has('capacity')) {
                    return;
                }
                $taken = $room->bookings()->where('status', 'approved')
                    ->selectRaw('semester, COUNT(*) as total')->groupBy('semester')->pluck('total')->max() ?? 0;
                if ((int) $this->input('capacity') < $taken) {
                    $validator->errors()->add('capacity', "This room already has {$taken} approved students in one semester.");
                }
            },
        ];
    }
}
