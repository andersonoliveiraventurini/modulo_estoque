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
        Schema::create('fornecedores', function (Blueprint $table) {
            $table->id();
            $table->enum('status', ['ativo', 'inativo', 'bloqueado'])->default('ativo');
            $table->integer('linha_brcom')->nullable();
            $table->string('nome_fantasia')->nullable();
            $table->string('razao_social')->nullable();
            $table->string('tratamento')->nullable();
            $table->string('inscricao_estadual')->nullable();
            $table->string('inscricao_municipal')->nullable();
            $table->date('data_abertura')->nullable();
            $table->string('cnae_principal')->nullable();
            $table->string('regime_tributario')->nullable();
            $table->text('descricao')->nullable();
            $table->text('observacao')->nullable();
            $table->string('email')->nullable();
            $table->string('telefone')->nullable();
            $table->string('cnpj')->unique()->nullable();

            // Benefícios e Certificações
            $table->string('beneficio')->nullable();
            $table->string('certidoes_negativas')->nullable();
            $table->string('certificacoes')->nullable();

            // Status
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fornecedores');
    }
};
