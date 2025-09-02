<?php

namespace Tests\Feature\Auth;

use App\Models\AdicionaisIndicacao;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_block_invalid_data(): void
    {
        $response = $this->postJson('/register', [
            'name' => str_repeat('A', 300),
            'username' => 'test user',
            'email' => 'test@@example.com',
            'password' => str_repeat('a', 300),
            // 'cpf' => '123',
            // 'whatsapp' => '111',
            'indicated_by' => '123',
            'fingerprint' => '123',
        ]);

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'name',
            'username',
            'email',
            // 'cpf',
            // 'whatsapp',
            'indicated_by'
        ]);

        $errors = $response->json('errors');

        $this->assertStringContainsString('O campo nome não pode ser superior a 255 caractere', $errors['name'][0]);
        $this->assertStringContainsString('formato inválido', $errors['username'][0]); // regex
        $this->assertStringContainsString('deve ser um endereço de e-mail válido', $errors['email'][0]);
        // $this->assertStringContainsString('O cpf informado não é valido', $errors['cpf'][0]); // Cpf custom rule
        // $this->assertStringContainsString('formato inválido', $errors['whatsapp'][0]);
        $this->assertStringContainsString('O campo indicated by selecionado é inválido.', $errors['indicated_by'][0]);

        $this->assertGuest();
    }

    public function test_block_if_users_has_many_accounts(): void
    {
        User::factory()->count(env('MAX_REG_PER_FINGERPOINT'))->create([
            'fingerprint' => 'teste-fingerprint-123',
        ]);

        session(['fingerprint' => 'teste-fingerprint-123']);
        $response = $this->postJson('/register', [
            'name' => str_repeat('A', 30),
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => str_repeat('a', 8),
            // 'cpf' => '123.456.789-09',
            // 'whatsapp' => '(11) 99387-0997',
        ]);

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'fingerprint',
        ]);

        $errors = $response->json('errors');

        $this->assertStringContainsString('Você já tem muitos cadastros, entre em contato com nossa equipe caso precise fazer mais cadastros.', $errors['fingerprint'][0]);

        $this->assertGuest();
    }


    public function test_new_users_can_register(): void
    {
        session(['fingerprint' => 'teste-fingerprint-123']);

        $data =  [
            'name' => str_repeat('A', 30),
            'username' => 'testuser',
            'email' => 'test@gmail.com',
            'password' => str_repeat('a', 8),
            // 'cpf' => '123.456.789-09',
            // 'whatsapp' => '(11) 99387-0997',
        ];
        $response = $this->postJson('/register', $data);

        $data['fingerprint'] =  'teste-fingerprint-123';
        unset($data['password']);

        $this->assertDatabaseHas('users', $data);
        $this->assertAuthenticated();

        $response->assertRedirect(route('home', absolute: false));
    }

    public function test_indications_add_point_correctly(): void
    {

        $indicatedBy = User::factory()->create();
        session(['fingerprint' => 'teste-fingerprint-123']);

        $data =  [
            'name' => str_repeat('A', 30),
            'username' => 'testuser',
            'email' => 'test@gmail.com',
            'password' => str_repeat('a', 8),
            // 'cpf' => '123.456.789-09',
            // 'whatsapp' => '(11) 9999-9999',
            'indicated_by' => $indicatedBy->uuid
        ];
        $response = $this->post('/register', $data);

        $data['fingerprint'] =  'teste-fingerprint-123';
        unset($data['password']);

        $this->assertDatabaseHas('users', $data);
        $this->assertDatabaseHas('adicionais_indicacao', ['user_uuid' => $indicatedBy->uuid, 'value' => env('INDICATION_ADICIONAL', 5)]);
        $this->assertAuthenticated();

        $response->assertRedirect(route('home', absolute: false));
    }

    public function test_indications_sun_point_correctly(): void
    {

        $indicatedBy = User::factory()->create();

        AdicionaisIndicacao::create(['user_uuid' => $indicatedBy->uuid, 'value' => env('INDICATION_ADICIONAL', 5)]);
        session(['fingerprint' => 'teste-fingerprint-123']);

        $data =  [
            'name' => str_repeat('A', 30),
            'username' => 'testuser',
            'email' => 'test@gmail.com',
            'password' => str_repeat('a', 8),
            // 'cpf' => '123.456.789-09',
            // 'whatsapp' => '(11) 9999-9999',
            'indicated_by' => $indicatedBy->uuid
        ];
        $response = $this->post('/register', $data);

        $data['fingerprint'] =  'teste-fingerprint-123';
        unset($data['password']);

        $this->assertDatabaseHas('users', $data);
        $this->assertDatabaseHas('adicionais_indicacao', ['user_uuid' => $indicatedBy->uuid, 'value' => env('INDICATION_ADICIONAL', 5) + env('INDICATION_ADICIONAL', 5)]);
        $this->assertAuthenticated();

        $response->assertRedirect(route('home', absolute: false));
    }
}
