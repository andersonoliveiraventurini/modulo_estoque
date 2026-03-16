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
        if (!Schema::hasColumn('armazens', 'descricao')) {
            Schema::table('armazens', function (Blueprint $table) {
                $table->text('descricao')->nullable()->after('localizacao');
            });
        }

        if (!Schema::hasColumn('corredors', 'descricao')) {
            Schema::table('corredors', function (Blueprint $table) {
                $table->text('descricao')->nullable()->after('nome');
            });
        }

        if (!Schema::hasColumn('posicaos', 'descricao')) {
            Schema::table('posicaos', function (Blueprint $table) {
                $table->text('descricao')->nullable()->after('nome');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('armazens', 'descricao')) {
            Schema::table('armazens', function (Blueprint $table) {
                $table->dropColumn('descricao');
            });
        }

        if (Schema::hasColumn('corredors', 'descricao')) {
            Schema::table('corredors', function (Blueprint $table) {
                $table->dropColumn('descricao');
            });
        }

        if (Schema::hasColumn('posicaos', 'descricao')) {
            Schema::table('posicaos', function (Blueprint $table) {
                $table->dropColumn('descricao');
            });
        }
    }
};
