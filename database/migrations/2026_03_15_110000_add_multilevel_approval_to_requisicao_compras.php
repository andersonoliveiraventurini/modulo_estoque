<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('requisicao_compras', function (Blueprint $table) {
            $table->integer('nivel_aprovacao')->default(1)->after('status')->comment('1: Supervisor, 2: Gerente, 3: Diretor');
            $table->json('aprovacoes_json')->nullable()->after('nivel_aprovacao');
            $table->timestamp('rejeitado_em')->nullable()->after('aprovado_em');
            $table->foreignId('rejeitado_por_id')->nullable()->after('rejeitado_em')->constrained('users');
        });
    }

    public function down(): void
    {
        Schema::table('requisicao_compras', function (Blueprint $table) {
            $table->dropColumn(['nivel_aprovacao', 'aprovacoes_json', 'rejeitado_em']);
            $table->dropConstrainedForeignId('rejeitado_por_id');
        });
    }
};
