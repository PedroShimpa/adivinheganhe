<?php

namespace Tests\Feature\Auth;

use App\Models\AdivinheOMilhao\InicioJogo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IniciarTest extends TestCase
{
    use RefreshDatabase;


    public function test_block_unauthenticated(): void
    {
        $response = $this->getJson(route('adivinhe_o_milhao.iniciar'));

        $response->assertStatus(401);
    }

    public function test_start_of_zero_when_the_player_already_played(): void
    {
        $user = User::factory()->create();

        InicioJogo::factory()->create(['finalizado' => 0, 'created_at' => now()->subDays(1), 'user_id' => $user->id]);

        $this->actingAs($user);

        $this->assertDatabaseCount('adivinhe_o_milhao_inicio_jogo', 1);

        $this->get(route('adivinhe_o_milhao.iniciar'))->assertRedirect();
        $this->assertDatabaseCount('adivinhe_o_milhao_inicio_jogo', 2);
    }
}
