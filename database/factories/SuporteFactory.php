<?php

namespace Database\Factories;

use App\Models\Suporte;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Suporte>
 */
class SuporteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nome' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'user_id' => User::factory(),
            'categoria_id' => 1, // Assuming a default category
            'descricao' => fake()->paragraph(),
            'status' => 'F',
            'admin_response' => null,
            'attachments' => null,
        ];
    }
}
