<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('requisicao_compras')) {
            Schema::create('requisicao_compras', function (Blueprint $table) {
                $table->id();
                $table->foreignId('solicitante_id')->constrained('users');
                $table->foreignId('aprovador_id')->nullable()->constrained('users');
                $table->timestamp('data_requisicao');
                $table->enum('status', ['pendente', 'aprovada', 'rejeitada', 'convertida'])->default('pendente');
                $table->text('observacao')->nullable();
                $table->decimal('valor_estimado', 15, 2)->default(0);
                $table->timestamp('aprovado_em')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('requisicao_compra_itens')) {
            Schema::create('requisicao_compra_itens', function (Blueprint $table) {
                $table->id();
                $table->foreignId('requisicao_compra_id')->constrained('requisicao_compras')->onDelete('cascade');
                $table->foreignId('produto_id')->nullable()->constrained('produtos');
                $table->string('descricao_livre')->nullable();
                $table->decimal('quantidade', 15, 3);
                $table->decimal('valor_unitario_estimado', 15, 2)->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('requisicao_compra_itens');
        Schema::dropIfExists('requisicao_compras');
    }
};
