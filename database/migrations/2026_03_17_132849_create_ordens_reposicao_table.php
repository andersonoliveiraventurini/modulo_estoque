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
        Schema::create('ordens_reposicao', function (Blueprint $table) {
            $table->id();
            $table->foreignId('produto_id')->constrained('produtos')->cascadeOnDelete();
            
            $table->decimal('quantidade_solicitada', 15, 2);
            
            // pendente, em_execucao, concluida, cancelada
            $table->string('status')->default('pendente');
            
            $table->foreignId('solicitado_por_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('executor_id')->nullable()->constrained('users')->nullOnDelete();
            
            $table->foreignId('armazem_origem_id')->nullable()->constrained('armazens')->nullOnDelete();
            $table->foreignId('corredor_origem_id')->nullable()->constrained('corredores')->nullOnDelete();
            $table->foreignId('posicao_origem_id')->nullable()->constrained('posicoes')->nullOnDelete();
            
            $table->timestamp('impresso_em')->nullable();
            $table->timestamp('concluido_em')->nullable();
            
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ordens_reposicao');
    }
};
