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
        Schema::table('conferencias', function (Blueprint $colunas) {
            $colunas->foreignId('usa_conferencia_anterior_id')
                ->nullable()
                ->after('outros_embalagem')
                ->constrained('conferencias')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('conferencias', function (Blueprint $colunas) {
            $colunas->dropForeign(['usa_conferencia_anterior_id']);
            $colunas->dropColumn('usa_conferencia_anterior_id');
        });
    }
};
