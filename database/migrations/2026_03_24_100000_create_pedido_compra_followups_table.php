<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pedido_compra_followups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pedido_compra_id')->constrained('pedido_compras')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users');
            $table->text('descricao');
            $table->date('previsao_nova')->nullable();
            $table->enum('tipo', ['cobranca', 'atualizacao_prazo', 'observacao'])->default('cobranca');
            $table->timestamps();
            $table->softDeletes();
        });
    }
    public function down(): void { Schema::dropIfExists('pedido_compra_followups'); }
};
