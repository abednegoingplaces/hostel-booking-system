<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Hostel>
 */
class HostelFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->randomElement(['Elgon', 'Aberdare', 'Turkana', 'Naivasha', 'Baringo', 'Kilifi', 'Meru', 'Kerio'])
                . ' ' . fake()->randomElement(['Hall', 'Court', 'House']),
            'location' => fake()->randomElement(['Main Campus', 'Town Campus', 'North Gate']) . ', Block ' . fake()->randomLetter(),
            'gender' => fake()->randomElement(config('hostel.genders')),
            'description' => fake()->sentence(10),
        ];
    }
}
