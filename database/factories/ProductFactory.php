<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->words(3, true);

        return [
            'name'        => $name,
            'slug'        => Str::slug($name, '-'),
            'description' => $this->faker->sentence(),
            'price'       => $this->faker->randomFloat(2, 50, 2000),
            'stock'       => $this->faker->numberBetween(0, 100),
            'sku'         => strtoupper(Str::random(8)),
            'is_active'   => $this->faker->boolean(90),
        ];
    }
}
