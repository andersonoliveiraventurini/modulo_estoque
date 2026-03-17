<?php

namespace App\Services;

use GuzzleHttp\Client;
use App\Models\Cliente;
use App\Models\Orcamento;
use Illuminate\Support\Facades\Log;

class RdStationService
{
    protected string $token;
    protected string $userId;
    protected Client $client;

    public function __construct()
    {
        $this->token = config('app.rdstation.token', '');
        $this->userId = config('app.rdstation.user_id', '');
        $this->client = new Client([
            'base_uri' => 'https://crm.rdstation.com/api/v1/',
            'headers' => [
                'accept' => 'application/json',
                'content-type' => 'application/json',
            ],
        ]);
    }

    /**
     * Sincroniza a empresa (Organização) no RD Station.
     */
    public function sincronizarEmpresa(Cliente $cliente): ?string
    {
        if (empty($this->token)) return null;

        try {
            $data = [
                'organization' => [
                    'name' => $cliente->nome_fantasia ?: $cliente->razao_social,
                    'user_id' => $this->userId,
                    'organization_custom_fields' => [
                        ['custom_field_id' => '67c8bcc7d016550014fdf760', 'value' => $cliente->cnpj],
                        ['custom_field_id' => '67c8bcc92912da00141c9e46', 'value' => $cliente->email],
                        ['custom_field_id' => '67c8bcde01ee1b0014100eba', 'value' => $cliente->telefone],
                    ]
                ]
            ];

            if ($cliente->rdstation_id) {
                $response = $this->client->put("organizations/{$cliente->rdstation_id}?token={$this->token}", ['json' => $data]);
            } else {
                $response = $this->client->post("organizations?token={$this->token}", ['json' => $data]);
            }

            if ($response->getStatusCode() === 200) {
                $body = json_decode($response->getBody(), true);
                $cliente->update(['rdstation_id' => $body['id'], 'enviado_api' => true]);
                return $body['id'];
            }
        } catch (\Exception $e) {
            Log::error("[RD STATION] Erro ao sincronizar empresa: " . $e->getMessage());
        }

        return null;
    }

    /**
     * Cria ou Ganha uma Negociação no RD Station.
     */
    public function registrarVenda(Orcamento $orcamento): bool
    {
        if (empty($this->token)) return false;

        $cliente = $orcamento->cliente;
        $orgId = $cliente->rdstation_id ?: $this->sincronizarEmpresa($cliente);

        if (!$orgId) return false;

        try {
            $data = [
                'deal' => [
                    'name' => "Orçamento #{$orcamento->id} - " . ($cliente->nome_fantasia ?: $cliente->razao_social),
                    'organization_id' => $orgId,
                    'user_id' => $this->userId,
                    'amount_total' => $orcamento->valor_com_desconto ?: $orcamento->valor_total_itens,
                    'prediction_date' => now()->format('Y-m-d'),
                    'deal_stage_id' => '67c8bcc4850d1d001fd0d990', // ID de estágio "Fechado/Ganho" - Ajustar se necessário
                    'rating' => 3
                ]
            ];

            $response = $this->client->post("deals?token={$this->token}", ['json' => $data]);

            if ($response->getStatusCode() === 200 || $response->getStatusCode() === 201) {
                Log::info("[RD STATION] Venda registrada para Orçamento #{$orcamento->id}");
                return true;
            }
        } catch (\Exception $e) {
            Log::error("[RD STATION] Erro ao registrar venda: " . $e->getMessage());
        }

        return false;
    }
}
