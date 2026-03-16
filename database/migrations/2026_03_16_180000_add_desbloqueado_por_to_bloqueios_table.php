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
        Schema::table('bloqueios', function (Blueprint $table) {
            if (!Schema::hasColumn('bloqueios', 'desbloqueado_por_id')) {
                $table->unsignedBigInteger('desbloqueado_por_id')->nullable()->after('user_id')
                    ->comment('Referência ao usuário que removeu o bloqueio.');
                $table->foreign('desbloqueado_por_id')->references('id')->on('users');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bloqueios', function (Blueprint $table) {
            if (Schema::hasColumn('bloqueios', 'desbloqueado_por_id')) {
                $table->dropForeign(['desbloqueado_por_id']);
                $table->dropColumn('desbloqueado_por_id');
            }
        });
    }
};
