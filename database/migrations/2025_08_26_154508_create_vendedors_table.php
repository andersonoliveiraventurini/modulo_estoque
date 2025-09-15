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
        Schema::create('vendedores', function (Blueprint $table) {
            $table->id();
            // quem deu o desconto
            $table->unsignedBigInteger('user_id')->nullable()
                    ->comment('Referência ao usuário que aplicou o desconto, se houver.');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->integer('externo')->default(0)
                    ->comment('Indica se o vendedor é externo (1) ou interno (0).');
            $table->string('desconto')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendedores');
    }
};
