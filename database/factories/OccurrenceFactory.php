<?php

namespace Database\Factories;

use App\Models\Occurrence;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Occurrence>
 */
class OccurrenceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'external_id' => 'EXT-' . $this->faker->unique()->bothify('####-??##'),
            'type' => $this->faker->randomElement([
                'incendio_urbano', 
                'resgate_veicular', 
                'atendimento_pre_hospitalar', 
                'salvamento_aquatico', 
                'falso_chamado'
            ]),
            'status' => Occurrence::STATUS_REPORTED,
            'description' => $this->faker->paragraph(),
            'reported_at' => now(),
        ];
    }
}
