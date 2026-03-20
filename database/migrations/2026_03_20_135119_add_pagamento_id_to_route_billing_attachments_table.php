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
        Schema::table('route_billing_attachments', function (Blueprint $table) {
            $table->foreignId('pagamento_id')->nullable()->after('orcamento_id')->constrained('pagamentos')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('route_billing_attachments', function (Blueprint $table) {
            $table->dropForeign(['pagamento_id']);
            $table->dropColumn('pagamento_id');
        });
    }
};
