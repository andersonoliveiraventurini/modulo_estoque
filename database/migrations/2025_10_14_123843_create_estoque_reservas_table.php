<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('estoque_reservas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('orcamento_id')->constrained('orcamentos');
            $table->foreignId('produto_id')->constrained('produtos');
            $table->decimal('quantidade', 12, 3);
            $table->enum('status', ['ativa', 'consumida', 'cancelada'])->default('ativa');
            $table->foreignId('criado_por_id')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void {
        Schema::dropIfExists('estoque_reservas');
    }
};