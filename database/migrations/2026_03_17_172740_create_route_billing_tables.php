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
        Schema::create('route_billing_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('orcamento_id')->constrained('orcamentos')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Vendedor que anexou
            $table->string('file_path');
            $table->string('file_type')->nullable(); // Ex: bilhete_unico, comprovante_pix, etc
            $table->text('notes')->nullable();
            $table->boolean('is_valid')->nullable(); // nulo = pendente, true = aprovado, false = recusado
            $table->foreignId('validated_by')->nullable()->constrained('users')->onDelete('set null'); // Financeiro q validou
            $table->timestamp('validated_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('route_billing_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('orcamento_id')->constrained('orcamentos')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Financeiro que aprovou/negou
            $table->enum('status', ['approved', 'rejected', 'restrictions']);
            $table->text('comments')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('route_billing_approvals');
        Schema::dropIfExists('route_billing_attachments');
    }
};
