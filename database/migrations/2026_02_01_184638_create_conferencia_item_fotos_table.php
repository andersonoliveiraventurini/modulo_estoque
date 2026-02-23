<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conferencia_item_fotos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conferencia_item_id')
                  ->constrained('conferencia_items')
                  ->cascadeOnDelete();
            $table->string('path');               // storage path relativo
            $table->string('disk')->default('public');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size')->nullable(); // bytes
            $table->text('legenda')->nullable();
            $table->foreignId('enviado_por_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('conferencia_item_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conferencia_item_fotos');
    }
};