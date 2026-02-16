<?php

namespace Database\Factories;

use App\Models\Occurrence;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Dispatch>
 */
class DispatchFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'occurrence_id' => Occurrence::factory(),
            'resource_code' => $this->faker->randomElement(['ABT-12', 'UR-05', 'ASU-02', 'AR-01']),
            'status' => 'assigned',
        ];
    }
}
