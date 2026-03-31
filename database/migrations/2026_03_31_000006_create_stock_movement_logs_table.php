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
        Schema::create('stock_movement_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('produto_id')->constrained('produtos')->cascadeOnDelete();
            $table->foreignId('posicao_id')->nullable()->constrained('posicoes')->nullOnDelete();
            $table->enum('tipo_movimentacao', ['entry', 'stock_transfer', 'replenishment', 'sale_output', 'manual_adjustment']);
            $table->decimal('quantidade', 12, 3);
            $table->foreignId('colaborador_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('observacao')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movement_logs');
    }
};
