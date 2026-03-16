<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('romaneios')) {
            Schema::create('romaneios', function (Blueprint $col) {
                $col->id();
                $col->string('descricao')->nullable();
                $col->string('motorista')->nullable();
                $col->string('veiculo')->nullable();
                $col->date('data_entrega')->nullable();
                $col->enum('status', ['aberto', 'em_transito', 'concluido', 'cancelado'])->default('aberto');
                $col->text('observacoes')->nullable();
                $col->foreignId('user_id')->constrained(); // Quem criou o romaneio
                $col->timestamps();
                $col->softDeletes();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('romaneios');
    }
};
