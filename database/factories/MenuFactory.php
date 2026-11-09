<?php

namespace Database\Factories;

use App\Models\Menu;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Menu>
 */
class MenuFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama' => fake()->words(2, true),
            'harga' => fake()->numberBetween(3, 25) * 1000,
            'tersedia' => true,
        ];
    }

    public function habis(): static
    {
        return $this->state(fn (array $attributes) => ['tersedia' => false]);
    }
}
