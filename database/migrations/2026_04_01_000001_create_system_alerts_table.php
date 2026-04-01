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
        if (!Schema::hasTable('system_alerts')) {
            Schema::create('system_alerts', function (Blueprint $table) {
                $table->id();
                $table->string('tipo')->comment('hub_zero, replenishment_needed, pending_approval');
                $table->string('mensagem');
                $table->foreignId('produto_id')->nullable()->constrained('produtos')->nullOnDelete();
                $table->foreignId('orcamento_id')->nullable()->constrained('orcamentos')->nullOnDelete();
                $table->boolean('lida')->default(false);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_alerts');
    }
};
