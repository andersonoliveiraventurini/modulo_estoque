<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CnpjService
{
    /**
     * Consulta o status de um CNPJ na BrasilAPI.
     *
     * @param string $cnpj
     * @return array|null
     */
    public function consultarCnpj(string $cnpj)
    {
        $cnpj = preg_replace('/\D/', '', $cnpj);

        if (strlen($cnpj) !== 14) {
            return null;
        }

        return Cache::remember("cnpj_v2_{$cnpj}", now()->addHours(24), function () use ($cnpj) {
            try {
                // Tentamos a V2 primeiro (mais completa)
                $response = Http::timeout(10)->get("https://brasilapi.com.br/api/cnpj/v2/{$cnpj}");

                if ($response->successful()) {
                    return $response->json();
                }

                // Fallback para V1 se a V2 falhar
                $responseV1 = Http::timeout(10)->get("https://brasilapi.com.br/api/cnpj/v1/{$cnpj}");

                if ($responseV1->successful()) {
                    return $responseV1->json();
                }

                return null;
            } catch (\Exception $e) {
                Log::error("Erro ao consultar CNPJ na BrasilAPI: " . $e->getMessage(), ['cnpj' => $cnpj]);
                return null;
            }
        });
    }

    /**
     * Verifica se a situação cadastral está ativa.
     *
     * @param array|null $data
     * @return bool
     */
    public function estaAtivo(?array $data): bool
    {
        if (!$data) {
            return true; // Se não conseguir consultar, não bloqueamos por padrão (ou tratamos como incerto)
        }

        $situacao = strtoupper(trim($data['descricao_situacao_cadastral'] ?? ''));
        return $situacao === 'ATIVA';
    }
}
