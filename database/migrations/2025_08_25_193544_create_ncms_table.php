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
        Schema::create('ncms', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 10)->index();
            $table->text('descricao');
            $table->date('data_inicio')->nullable();
            $table->date('data_fim')->nullable();
            $table->string('ato_legal')->nullable();
            $table->string('numero')->nullable();
            $table->string('ano')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ncms');
    }
};
