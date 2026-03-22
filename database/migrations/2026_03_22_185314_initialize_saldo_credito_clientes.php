<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Cliente;
use App\Models\ClienteCreditos;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $clientes = Cliente::all();
        
        foreach ($clientes as $cliente) {
            $saldo = (float) ClienteCreditos::where('cliente_id', $cliente->id)
                ->where('status', 'ativo')
                ->where('valor_disponivel', '>', 0)
                ->where(function($query) {
                    $query->whereNull('data_validade')
                          ->orWhere('data_validade', '>=', now());
                })
                ->sum('valor_disponivel');
            
            $cliente->update(['saldo_credito' => $saldo]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No reverse needed as the column will be dropped by individual migration
    }
};
