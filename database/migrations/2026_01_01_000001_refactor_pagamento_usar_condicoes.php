<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── 1. Adiciona campos em condicoes_pagamento ────────────────────────
        Schema::table('condicoes_pagamento', function (Blueprint $table) {
            $table->enum('tipo', [
                'dinheiro', 'cartao_credito', 'cartao_debito', 'pix',
                'boleto', 'cheque', 'credito_cliente', 'transferencia', 'outros',
            ])->default('outros')->after('nome')
              ->comment('Tipo financeiro da condição — define regras de desconto e crédito');

            $table->boolean('permite_parcelamento')->default(false)->after('tipo');
            $table->integer('max_parcelas')->nullable()->after('permite_parcelamento');
            $table->boolean('ativo')->default(true)->after('max_parcelas');
            $table->integer('ordem')->default(0)->after('ativo');
        });

        // ── 2. Altera pagamento_formas ───────────────────────────────────────
        //
        // Estado do banco após migrate:fresh (DDL original):
        //
        //   CONSTRAINT pagamento_formas_metodo_pagamento_id_foreign  → FK em metodo_pagamento_id
        //   CONSTRAINT pagamento_formas_pagamento_id_foreign          → FK em pagamento_id
        //   KEY pagamento_formas_metodo_pagamento_id_foreign          → índice simples (sustenta a FK acima)
        //   KEY pagamento_formas_pagamento_id_metodo_pagamento_id_index → índice composto
        //
        // Regra do MySQL: um índice só pode ser removido depois que TODAS as FKs
        // que o utilizam (mesmo indiretamente) forem removidas.
        // Por isso usamos três ALTER TABLE separados.

        // PASSO A — remove as duas FKs (libera ambos os índices)
        Schema::table('pagamento_formas', function (Blueprint $table) {
            $table->dropForeign('pagamento_formas_metodo_pagamento_id_foreign');
            $table->dropForeign('pagamento_formas_pagamento_id_foreign');
        });

        // PASSO B — agora sem FKs, remove os índices e a coluna antiga
        Schema::table('pagamento_formas', function (Blueprint $table) {
            $table->dropIndex('pagamento_formas_pagamento_id_metodo_pagamento_id_index');
            $table->dropIndex('pagamento_formas_metodo_pagamento_id_foreign');
            $table->dropColumn('metodo_pagamento_id');
        });

        // PASSO C — adiciona nova FK + recria FK de pagamento_id + novo índice composto
        Schema::table('pagamento_formas', function (Blueprint $table) {
            $table->foreignId('condicao_pagamento_id')
                ->after('pagamento_id')
                ->constrained('condicoes_pagamento')
                ->comment('Condição de pagamento usada nesta forma');

            $table->foreign('pagamento_id')
                ->references('id')->on('pagamentos')
                ->cascadeOnDelete();

            $table->index(['pagamento_id', 'condicao_pagamento_id']);
        });

        // ── 3. Cria pagamento_comprovantes ───────────────────────────────────
        Schema::create('pagamento_comprovantes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pagamento_id')
                ->constrained('pagamentos')
                ->cascadeOnDelete()
                ->comment('Pagamento ao qual o comprovante pertence');

            $table->foreignId('pagamento_forma_id')
                ->nullable()
                ->constrained('pagamento_formas')
                ->nullOnDelete()
                ->comment('Forma específica à qual o comprovante se refere (opcional)');

            $table->string('nome_original')
                ->comment('Nome original do arquivo enviado pelo atendente');

            $table->string('path')
                ->comment('Caminho no disco private: pagamentos/{id}/comprovantes/{filename}');

            $table->string('mime_type', 100)
                ->comment('MIME type: application/pdf, image/jpeg, image/png, image/webp');

            $table->unsignedBigInteger('tamanho')
                ->comment('Tamanho do arquivo em bytes');

            $table->foreignId('user_id')
                ->constrained('users')
                ->comment('Atendente que fez o upload');

            $table->timestamps();
            $table->softDeletes();

            $table->index('pagamento_id');
            $table->index('pagamento_forma_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagamento_comprovantes');

        // PASSO A — remove FKs
        Schema::table('pagamento_formas', function (Blueprint $table) {
            $table->dropForeign(['condicao_pagamento_id']);
            $table->dropForeign(['pagamento_id']);
        });

        // PASSO B — remove índices e coluna nova
        Schema::table('pagamento_formas', function (Blueprint $table) {
            $table->dropIndex(['pagamento_id', 'condicao_pagamento_id']);
            $table->dropColumn('condicao_pagamento_id');
        });

        // PASSO C — recria estrutura original
        Schema::table('pagamento_formas', function (Blueprint $table) {
            $table->foreignId('metodo_pagamento_id')
                ->constrained('metodos_pagamento');

            $table->foreign('pagamento_id')
                ->references('id')->on('pagamentos')
                ->cascadeOnDelete();

            $table->index(['pagamento_id', 'metodo_pagamento_id']);
        });

        Schema::table('condicoes_pagamento', function (Blueprint $table) {
            $table->dropColumn(['tipo', 'permite_parcelamento', 'max_parcelas', 'ativo', 'ordem']);
        });
    }
};