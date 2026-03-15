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
        Schema::table('movimentacoes', function (Blueprint $table) {
            $table->enum('status', ['pendente', 'aprovado', 'rejeitado'])->default('pendente')->after('tipo');
            $table->foreignId('supervisor_id')->nullable()->after('status')->constrained('users');
            $table->timestamp('aprovado_em')->nullable()->after('supervisor_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('movimentacoes', function (Blueprint $table) {
            $table->dropForeign(['supervisor_id']);
            $table->dropColumn(['status', 'supervisor_id', 'aprovado_em']);
        });
    }
};
