<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Precisamos usar DB::statement pois o Doctrine DBAL não suporta alteração de ENUM nativamente
        DB::statement("ALTER TABLE orcamentos MODIFY COLUMN status ENUM('Aprovar desconto','Aprovar pagamento', 'Pendente', 'Aprovado', 'Finalizado', 'Cancelado', 'Rejeitado', 'Expirado', 'Pago', 'Estornado', 'Sem estoque', 'Pagamento pendente') DEFAULT 'Pendente' COMMENT 'Status do orçamento, como pendente, aprovado, cancelado, etc.'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE orcamentos MODIFY COLUMN status ENUM('Aprovar desconto','Aprovar pagamento', 'Pendente', 'Aprovado', 'Finalizado', 'Cancelado', 'Rejeitado', 'Expirado', 'Pago', 'Estornado', 'Sem estoque') DEFAULT 'Pendente' COMMENT 'Status do orçamento, como pendente, aprovado, cancelado, etc.'");
    }
};
