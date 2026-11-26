<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Booking>
 */
class BookingFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'room_id' => Room::factory(),
            'semester' => config('hostel.default_semester'),
        ];
    }

    /** Status is not mass assignable, so set it on the model after making it. */
    public function status(string $status): static
    {
        return $this->afterMaking(fn (Booking $booking) => $booking->status = $status);
    }
}
