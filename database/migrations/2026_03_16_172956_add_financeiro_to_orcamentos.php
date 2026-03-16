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
        Schema::table('orcamentos', function (Blueprint $table) {
            $table->timestamp('enviado_financeiro_em')->nullable();
            $table->foreignId('enviado_financeiro_por_id')->nullable()->constrained('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orcamentos', function (Blueprint $table) {
            $table->dropForeign(['enviado_financeiro_por_id']);
            $table->dropColumn(['enviado_financeiro_em', 'enviado_financeiro_por_id']);
        });
    }
};
