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
        if (Schema::hasTable('corredors') && !Schema::hasTable('corredores')) {
            Schema::rename('corredors', 'corredores');
        }

        if (Schema::hasTable('posicaos') && !Schema::hasTable('posicoes')) {
            Schema::rename('posicaos', 'posicoes');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('corredores') && !Schema::hasTable('corredors')) {
            Schema::rename('corredores', 'corredors');
        }

        if (Schema::hasTable('posicoes') && !Schema::hasTable('posicaos')) {
            Schema::rename('posicoes', 'posicaos');
        }
    }
};
