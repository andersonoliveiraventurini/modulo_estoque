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
        if (!Schema::hasTable('posicaos')) {
            Schema::create('posicaos', function (Blueprint $table) {
                $table->id();
                // Custom constraint name to avoid duplicate names if it failed halfway in the past
                $table->foreignId('corredor_id')
                      ->constrained('corredors', 'id', 'fk_posicaos_corredor_id')
                      ->cascadeOnDelete();
                $table->string('nome');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posicaos');
    }
};
