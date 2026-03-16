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
        if (!Schema::hasTable('corredors')) {
            Schema::create('corredors', function (Blueprint $table) {
                $table->id();
                // Custom constraint name
                $table->foreignId('armazem_id')
                      ->constrained('armazens', 'id', 'fk_corredors_armazem_id')
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
        Schema::dropIfExists('corredors');
    }
};
