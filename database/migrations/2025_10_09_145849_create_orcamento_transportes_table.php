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
        Schema::create('orcamento_transportes', function (Blueprint $table) {
            $table->id(); 
            $table->foreignId('orcamento_id')->constrained()->onDelete('cascade');
            $table->foreignId('tipo_transporte_id')->constrained('tipos_transportes')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orcamento_transportes');
    }
};
