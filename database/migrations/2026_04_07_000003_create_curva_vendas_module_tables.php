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
        // Add classification fields to produtos table
        Schema::table('produtos', function (Blueprint $table) {
            $table->string('classificacao_curva', 2)->nullable();
            $table->boolean('classificacao_manual')->default(false);
            $table->text('justificativa_manual')->nullable();
        });

        // Create configuration table
        Schema::create('curva_vendas_configs', function (Blueprint $table) {
            $table->id();
            $table->date('periodo_inicio');
            $table->date('periodo_fim');
            $table->json('parametros'); // Array with up to 3 parameters and their ranges
            $table->timestamps();
        });

        // Create audit table for manual changes
        Schema::create('curva_vendas_auditoria', function (Blueprint $table) {
            $table->id();
            $table->foreignId('produto_id')->constrained('produtos')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('de', 2)->nullable();
            $table->string('para', 2);
            $table->text('justificativa');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('curva_vendas_auditoria');
        Schema::dropIfExists('curva_vendas_configs');
        Schema::table('produtos', function (Blueprint $table) {
            $table->dropColumn(['classificacao_curva', 'classificacao_manual', 'justificativa_manual']);
        });
    }
};
