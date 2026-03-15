<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('faturas')) {
            Schema::create('faturas', function (Blueprint $table) {
                $table->id();
                
                $table->foreignId('cliente_id')->constrained('clientes')->onDelete('cascade');
                $table->foreignId('orcamento_id')->nullable()->constrained('orcamentos')->onDelete('set null');
                $table->foreignId('pedido_id')->nullable()->constrained('pedidos')->onDelete('set null');
                
                $table->decimal('valor_total', 15, 2);
                $table->decimal('valor_pago', 15, 2)->default(0);
                
                $table->integer('numero_parcela')->default(1);
                $table->integer('total_parcelas')->default(1);
                
                $table->date('data_vencimento');
                $table->datetime('data_pagamento')->nullable();
                
                $table->enum('status', ['pendente', 'parcial', 'pago', 'vencido', 'cancelado'])->default('pendente');
                
                $table->text('observacoes')->nullable();
                
                $table->timestamps();
                $table->softDeletes();
                
                $table->index(['status', 'data_vencimento']);
                $table->index('cliente_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('faturas');
    }
};
