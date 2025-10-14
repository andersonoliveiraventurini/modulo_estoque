<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('picking_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('orcamento_id')->constrained('orcamentos');
            $table->foreignId('armazem_id')->nullable()->constrained('armazens');
            $table->enum('status', ['aberto', 'em_separacao', 'concluido', 'cancelado'])->default('aberto');
            $table->text('observacoes')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->foreignId('criado_por_id')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('picking_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('picking_batch_id')->constrained('picking_batches')->cascadeOnDelete();
            $table->foreignId('orcamento_item_id')->nullable()->constrained('orcamento_itens')->nullOnDelete();
            $table->foreignId('produto_id')->constrained('produtos');

            $table->decimal('qty_solicitada', 12, 3);
            $table->decimal('qty_separada', 12, 3)->default(0);
            $table->enum('status', ['pendente', 'parcial', 'separado', 'cancelado'])->default('pendente');

            $table->string('localizacao')->nullable();

            // autoria da separação
            $table->foreignId('separado_por_id')->nullable()->constrained('users');
            $table->timestamp('separado_em')->nullable();

            // motivo de não separação (quando qty=0 na ação)
            $table->text('motivo_nao_separado')->nullable();

            // inconsistência durante separação
            $table->boolean('inconsistencia_reportada')->default(false);
            $table->foreignId('inconsistencia_por_id')->nullable()->constrained('users');
            $table->text('inconsistencia_obs')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void {
        Schema::dropIfExists('picking_items');
        Schema::dropIfExists('picking_batches');
    }
};