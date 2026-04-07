<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('relatorios_estoque_minimo', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique();
            $table->foreignId('user_id')->constrained('users');
            $table->json('parametros');
            $table->string('status')->default('concluido');
            $table->integer('total_itens')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('relatorios_estoque_minimo');
    }
};
