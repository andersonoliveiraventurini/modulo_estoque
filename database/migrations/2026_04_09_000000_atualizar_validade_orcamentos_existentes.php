<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Esta migration atualiza os orçamentos existentes no banco de dados,
     * recalculando a validade de 2 para 5 dias para orçamentos pendentes.
     */
    public function up(): void
    {
        // 1. Atualizar orçamentos pendentes ou sem estoque que ainda não expiraram
        // Recalculamos a validade baseada no created_at + 5 dias.
        // Isso garante que orçamentos criados há 3 ou 4 dias, que expirariam hoje,
        // agora tenham sua validade estendida para 5 dias totais.
        
        DB::table('orcamentos')
            ->whereIn('status', ['Pendente', 'Sem estoque'])
            ->whereNotNull('validade')
            ->whereNotNull('created_at')
            ->orderBy('id')
            ->chunkById(100, function ($orcamentos) {
                foreach ($orcamentos as $orcamento) {
                    $createdAt = Carbon::parse($orcamento->created_at);
                    
                    // Definimos a nova validade como 5 dias após a criação.
                    // Isso sobrescreve a validade de 2 dias anterior.
                    DB::table('orcamentos')
                        ->where('id', $orcamento->id)
                        ->update([
                            'validade' => $createdAt->copy()->addDays(5)->toDateString(),
                            'updated_at' => now(),
                        ]);
                }
            });

        // 2. Atualizar grupos de cotação que estão com validade de 2 dias
        // (48 horas após ficar disponível) para 5 dias (120 horas).
        if (Schema::hasTable('consulta_preco_grupos')) {
            DB::table('consulta_preco_grupos')
                ->where('status', 'Disponível')
                ->whereNotNull('validade')
                ->whereNotNull('updated_at') // Validade conta a partir do momento que ficou disponível
                ->orderBy('id')
                ->chunkById(100, function ($grupos) {
                    foreach ($grupos as $grupo) {
                        $updatedAt = Carbon::parse($grupo->updated_at);
                        
                        DB::table('consulta_preco_grupos')
                            ->where('id', $grupo->id)
                            ->update([
                                'validade' => $updatedAt->copy()->addDays(5),
                            ]);
                    }
                });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverter a validade para 2 dias (caso necessário em rollback)
        DB::table('orcamentos')
            ->whereIn('status', ['Pendente', 'Sem estoque'])
            ->whereNotNull('validade')
            ->whereNotNull('created_at')
            ->orderBy('id')
            ->chunkById(100, function ($orcamentos) {
                foreach ($orcamentos as $orcamento) {
                    $createdAt = Carbon::parse($orcamento->created_at);
                    DB::table('orcamentos')
                        ->where('id', $orcamento->id)
                        ->update([
                            'validade' => $createdAt->copy()->addDays(2)->toDateString(),
                        ]);
                }
            });
    }
};
