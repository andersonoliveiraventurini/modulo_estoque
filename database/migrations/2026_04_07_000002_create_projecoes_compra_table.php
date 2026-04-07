<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projecoes_compra', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique();
            $table->foreignId('user_id')->constrained('users');
            $table->date('data_pedido');
            $table->date('previsao_recebimento');
            $table->integer('meses_compra');
            $table->boolean('abater_estoque_atual')->default(true);
            $table->boolean('abater_consumo_ate_recebimento')->default(true);
            $table->json('filtros')->nullable();
            $table->decimal('valor_total_estimado', 15, 2)->default(0);
            $table->integer('total_itens')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('projecao_compra_itens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('projecao_compra_id')->constrained('projecoes_compra')->onDelete('cascade');
            $table->foreignId('produto_id')->constrained('produtos');
            $table->decimal('consumo_mensal', 12, 2);
            $table->decimal('estoque_atual', 12, 2);
            $table->decimal('previsao_consumo_recebimento', 12, 2);
            $table->decimal('quantidade_sugerida', 12, 2);
            $table->decimal('valor_unitario', 12, 2);
            $table->boolean('abaixo_minimo')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projecao_compra_itens');
        Schema::dropIfExists('projecoes_compra');
    }
};
