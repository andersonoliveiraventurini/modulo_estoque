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
        Schema::create('imagens', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('produto_id')->nullable()
                ->comment('Referência ao produto associado a esta imagem.');
            $table->foreign('produto_id')->references('id')->on('produtos')->onDelete('cascade');
            $table->string('caminho')->comment('Caminho do arquivo de imagem - Armazena o caminho do arquivo de imagem associado ao produto.');
            $table->boolean('principal')->default(false)->comment('Indica se a imagem é a principal do produto.');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('imagems');
    }
};
