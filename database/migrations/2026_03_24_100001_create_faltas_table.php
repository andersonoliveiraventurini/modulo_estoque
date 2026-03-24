<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::dropIfExists('falta_itens');
        Schema::dropIfExists('faltas');
        Schema::create('faltas', function (Blueprint $table) {
            $table->id();
            $table->string('numero_falta')->unique();
            $table->foreignId('user_id')->constrained('users');
            $table->unsignedBigInteger('vendedor_id')->nullable();
            $table->string('nome_cliente')->nullable();
            $table->foreignId('cliente_id')->nullable()->constrained('clientes')->nullOnDelete();
            $table->decimal('valor_total', 12, 2)->default(0);
            $table->text('observacao')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }
    public function down(): void { Schema::dropIfExists('faltas'); }
};
