<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** The JSON shape of one room in the API. */
class RoomResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'room_number' => $this->room_number,
            'hostel' => [
                'id' => $this->hostel->id,
                'name' => $this->hostel->name,
                'gender' => $this->hostel->gender,
            ],
            'capacity' => $this->capacity,
            'free_beds' => $this->free_beds,
            'price_per_semester' => (float) $this->price_per_semester,
            'photo_url' => $this->photo_url,
        ];
    }
}
