<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Adivinhacoes>
 */
class AdivinhacoesFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'titulo' => fake()->sentence(),
            'imagem' => fake()->imageUrl(),
            'descricao' => fake()->paragraph(),
            'premio' => fake()->randomFloat(2, 10, 1000),
            'resposta' => fake()->word(),
            'resolvida' => 'N',
            'liberado_at' => now(),
            'vip_release_at' => null,
            'only_members' => '0',
            'expire_at' => now()->addDays(7),
            'exibir_home' => 'S',
            'dica' => fake()->sentence(),
            'dica_paga' => 'N',
            'dica_valor' => 0,
            'visualizacoes' => 0,
            'formato_resposta' => 'text',
            'notificar_whatsapp' => '0',
            'notificar_push' => '0',
            'notificar_email' => '0',
            'notificado_email_em' => null,
            'notificado_whatsapp_em' => null,
            'dificuldade' => 'facil',
        ];
    }
}
