<?php

namespace App\Services;

use GuzzleHttp\Client;
use App\Models\Produto;
use App\Models\Pedido;
use Illuminate\Support\Facades\Log;

class WooCommerceService
{
    protected Client $client;
    protected string $url;
    protected string $consumerKey;
    protected string $consumerSecret;

    public function __construct()
    {
        $this->url = config('services.woocommerce.url', '');
        $this->consumerKey = config('services.woocommerce.key', '');
        $this->consumerSecret = config('services.woocommerce.secret', '');

        $this->client = new Client([
            'base_uri' => $this->url . '/wp-json/wc/v3/',
            'auth' => [$this->consumerKey, $this->consumerSecret],
            'headers' => [
                'accept' => 'application/json',
                'content-type' => 'application/json',
            ],
        ]);
    }

    /**
     * Sincroniza o estoque de um produto no ERP com o WooCommerce.
     */
    public function atualizarEstoqueNoSite(Produto $produto): bool
    {
        if (!$produto->sku || empty($this->url)) return false;

        try {
            // Primeiro, busca o produto pelo SKU no WooCommerce
            $response = $this->client->get('products', [
                'query' => ['sku' => $produto->sku]
            ]);

            $products = json_decode($response->getBody(), true);

            if (count($products) > 0) {
                $wcProductId = $products[0]['id'];
                
                // Atualiza a quantidade
                $this->client->put("products/{$wcProductId}", [
                    'json' => [
                        'manage_stock' => true,
                        'stock_quantity' => (int) $produto->estoque_atual
                    ]
                ]);

                Log::info("[WOOCOMMERCE] Estoque atualizado para SKU {$produto->sku}: {$produto->estoque_atual}");
                return true;
            }
        } catch (\Exception $e) {
            Log::error("[WOOCOMMERCE] Erro ao atualizar estoque: " . $e->getMessage());
        }

        return false;
    }

    /**
     * Importa novos pedidos do WooCommerce.
     */
    public function importarPedidos(): int
    {
        if (empty($this->url)) return 0;

        try {
            $response = $this->client->get('orders', [
                'query' => [
                    'status' => 'processing', // Apenas pedidos pagos
                    'after' => now()->subDay()->toIso8601String()
                ]
            ]);

            $orders = json_decode($response->getBody(), true);
            $count = 0;

            foreach ($orders as $order) {
                // Lógica de importação para a tabela de Pedidos/Orçamentos do ERP
                // ...
                $count++;
            }

            return $count;
        } catch (\Exception $e) {
            Log::error("[WOOCOMMERCE] Erro ao importar pedidos: " . $e->getMessage());
        }

        return 0;
    }
}
