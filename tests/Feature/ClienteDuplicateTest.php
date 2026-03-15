<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\User;
use App\Models\Vendedor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClienteDuplicateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create());
    }

    public function test_store_duplicate_cnpj_redirects_without_input_and_shows_link()
    {
        $existingClient = Cliente::factory()->create([
            'cnpj' => '12345678000199',
            'nome' => 'Cliente Existente'
        ]);

        $response = $this->post(route('clientes.store'), [
            'cnpj' => '12.345.678/0001-99',
            'razao_social' => 'Nova Empresa',
            'nome_fantasia' => 'Novo Fantasia',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('duplicate_client_id', $existingClient->id);
        $response->assertSessionHas('error');
        
        // Assert old input is NOT present (cleared form)
        $this->assertEmpty(session()->getOldInput());
    }

    public function test_store_duplicate_cpf_redirects_without_input_and_shows_link()
    {
        $existingClient = Cliente::factory()->create([
            'cpf' => '12345678901',
            'nome' => 'Pessoa Existente'
        ]);

        $response = $this->post(route('clientes.store'), [
            'cpf' => '123.456.789-01',
            'nome' => 'Nova Pessoa',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('duplicate_client_id', $existingClient->id);
        
        // Assert old input is NOT present (cleared form)
        $this->assertEmpty(session()->getOldInput());
    }
}
