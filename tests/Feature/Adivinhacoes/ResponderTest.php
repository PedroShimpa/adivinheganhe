<?php

namespace Tests\Feature\Auth;

use App\Models\Adivinhacoes;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Cache;

class ResponderTest extends TestCase
{
    use RefreshDatabase;

    public function test_block_unauthenticated(): void
    {
        $response = $this->postJson('/responder');

        $response->assertStatus(401);
    }

    public function test_validation_fail(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = $this->postJson('/responder');

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'resposta',
            'adivinhacao_id',
        ]);
    }

    public function test_reply_non_existent_adivinhacao(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = $this->postJson('/responder', ['resposta' => 'teste', 'adivinhacao_id' => 1]);

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'adivinhacao_id',
        ]);
    }

    public function test_reply_resolved_adivinhacao(): void
    {


        $user = User::factory()->create();

        $adivinhacao = Adivinhacoes::create([
            'titulo' => 'teste',
            'imagem' => 'teste',
            'descricao' => 'teste',
            'premio' => 'teste',
            'resposta' => 'teste',
            'resolvida' => 'S'
        ]);

        $this->actingAs($user);


        $response = $this->postJson('/responder', ['resposta' => 'teste', 'adivinhacao_id' => $adivinhacao->id]);
        
        $response->assertStatus(200);

        $response->assertJson(["info" => "Esta adivinhação já foi adivinhada, obrigado por tentar!"]);
    }

    public function test_reply_expired_adivinhacao(): void
    {
        $user = User::factory()->create();

        $adivinhacao = Adivinhacoes::create([
            'titulo' => 'teste',
            'imagem' => 'teste',
            'descricao' => 'teste',
            'premio' => 'teste',
            'resposta' => 'teste',
            'resolvida' => 'N',
            'expire_at' => now()->subDay()
        ]);

        $this->actingAs($user);

        $response = $this->postJson('/responder', ['resposta' => 'teste', 'adivinhacao_id' => $adivinhacao->id]);

        $response->assertStatus(200);

        $response->assertJson(["info" => "Esta adivinhação expirou! Obrigado por tentar!"]);
    }
}
