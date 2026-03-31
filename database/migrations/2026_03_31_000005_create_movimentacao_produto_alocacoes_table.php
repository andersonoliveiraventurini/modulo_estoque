<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('movimentacao_produto_alocacoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('movimentacao_produto_id')
                  ->constrained('movimentacao_produtos')
                  ->cascadeOnDelete()
                  ->name('fk_alocacao_mov_prod_id');
            $table->foreignId('posicao_id')
                  ->constrained('posicoes')
                  ->cascadeOnDelete()
                  ->name('fk_alocacao_posicao_id');
            $table->decimal('quantidade', 12, 3);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movimentacao_produto_alocacoes');
    }
};
