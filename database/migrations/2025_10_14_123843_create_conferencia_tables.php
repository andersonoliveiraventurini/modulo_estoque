<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('conferencias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('orcamento_id')->constrained('orcamentos');
            $table->foreignId('picking_batch_id')->constrained('picking_batches');
            $table->enum('status', ['aberta', 'em_conferencia', 'concluida', 'cancelada'])->default('aberta');
            $table->foreignId('conferente_id')->nullable()->constrained('users');
            $table->text('observacoes')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('conferencia_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conferencia_id')->constrained('conferencias')->cascadeOnDelete();
            $table->foreignId('picking_item_id')->constrained('picking_items')->cascadeOnDelete();
            $table->foreignId('produto_id')->constrained('produtos');

            $table->decimal('qty_separada', 12, 3);
            $table->decimal('qty_conferida', 12, 3)->default(0);

            $table->enum('status', ['ok', 'divergente'])->default('ok');
            $table->decimal('divergencia', 12, 3)->default(0);
            $table->text('motivo_divergencia')->nullable();

            $table->foreignId('conferido_por_id')->nullable()->constrained('users');
            $table->timestamp('conferido_em')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void {
        Schema::dropIfExists('conferencia_items');
        Schema::dropIfExists('conferencias');
    }
};