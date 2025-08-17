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
        Schema::create('blocok_insumos', function (Blueprint $table) {
            $table->id();
            // produto que será usado como insumo
            $table->unsignedBigInteger('produto_id')->nullable()
                  ->comment('Referência ao produto relacionado a este item do Bloco K.');
            $table->foreign('produto_id')->references('id')->on('produtos')->onDelete('cascade');
            // quantidade do insumo
            $table->decimal('quantidade', 15, 2)->nullable()
                    ->comment('Quantidade do insumo utilizado na produção.');
            // unidade de medida do insumo
            $table->string('unidade_medida', 10)->nullable()
                    ->comment('Unidade de medida do insumo utilizado, como kg, g, L, etc.');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blocok_insumos');
    }
};
