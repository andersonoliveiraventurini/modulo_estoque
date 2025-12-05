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
        Schema::create('pagamento_formas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pagamento_id')->constrained('pagamentos')->onDelete('cascade');
            $table->foreignId('metodo_pagamento_id')->constrained('metodos_pagamento');
            $table->decimal('valor', 10, 2)->comment('Valor pago com esta forma');
            $table->boolean('usa_credito')->default(false)->comment('Se utilizou crédito do cliente');
            $table->integer('parcelas')->default(1)->comment('Número de parcelas');
            $table->decimal('valor_parcela', 10, 2)->nullable()->comment('Valor de cada parcela');
            $table->text('observacoes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['pagamento_id', 'metodo_pagamento_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pagamento_formas');
    }
};