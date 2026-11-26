<?php

namespace Database\Factories;

use App\Models\Hostel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Room>
 */
class RoomFactory extends Factory
{
    public function definition(): array
    {
        return [
            'hostel_id' => Hostel::factory(),
            'room_number' => strtoupper(fake()->unique()->bothify('?###')),
            'capacity' => fake()->numberBetween(1, 4),
            'price_per_semester' => fake()->randomElement([6500, 7000, 8500, 11000, 12000, 15000]),
        ];
    }
}
