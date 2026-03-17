<?php

namespace App\Services;

use App\Models\Produto;
use App\Models\Blocok;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class BlocokService
{
    /**
     * Gera os registros do Bloco K para um período específico.
     */
    public function gerarRegistrosParaPeriodo($dataInicio, $dataFim)
    {
        $reg0200 = $this->gerarRegistro0200();
        $regK200 = $this->gerarRegistroK200($dataFim);

        return Blocok::create([
            'k001' => '0', // Abertura do bloco com dados
            'k100' => $dataInicio->format('dmY') . '|' . $dataFim->format('dmY'),
            'k200' => $regK200,
            'k990' => 'Total de registros', // Simplificado para exemplo
            // Armazenamos o 0200 em um campo customizado ou tratamos na exportação
        ]);
    }

    /**
     * Registro 0200: Tabela de Identificação do Item
     */
    public function gerarRegistro0200()
    {
        $produtos = Produto::all();
        $linhas = [];

        foreach ($produtos as $produto) {
            // Formato: |0200|COD_ITEM|DESCR_ITEM|COD_BARRA|COD_ANT_ITEM|UNID_INV|TIPO_ITEM|COD_NCM|...
            $linha = [
                '0200',
                $produto->sku,
                $this->limparTexto($produto->nome),
                '', // COD_BARRA
                '', // COD_ANT_ITEM
                'UN', // UNID_INV (Simplificado)
                '04', // TIPO_ITEM (04 = Produto Acabado)
                $produto->ncm?->codigo ?? '00000000',
                '', '', ''
            ];
            $linhas[] = '|' . implode('|', $linha) . '|';
        }

        return implode("\n", $linhas);
    }

    /**
     * Registro K200: Estoque Escriturado
     */
    public function gerarRegistroK200($dataReferencia)
    {
        // Pega o saldo atual (simplificado, ideal seria saldo na dataFim)
        $produtos = Produto::where('estoque_atual', '>', 0)->get();
        $linhas = [];

        foreach ($produtos as $produto) {
            // Formato: |K200|DT_EST|COD_ITEM|QTD|IND_EST|COD_PART|
            $linha = [
                'K200',
                $dataReferencia->format('dmY'),
                $produto->sku,
                number_format($produto->estoque_atual, 3, '.', ''),
                '0', // IND_EST (0 = Estoque de propriedade do informante e em seu poder)
                ''   // COD_PART
            ];
            $linhas[] = '|' . implode('|', $linha) . '|';
        }

        return implode("\n", $linhas);
    }

    private function limparTexto($texto)
    {
        return str_replace(['|', "\n", "\r"], [' ', '', ''], $texto);
    }

    /**
     * Exporta o arquivo TXT completo do Bloco K.
     */
    public function exportarTxt(Blocok $bloco)
    {
        $conteudo = [];
        $conteudo[] = "|K001|{$bloco->k001}|";
        $conteudo[] = "|K100|{$bloco->k100}|";
        
        // Incluir 0200 (deve vir antes do K)
        $conteudo[] = $this->gerarRegistro0200();
        
        $conteudo[] = $bloco->k200;
        $conteudo[] = "|K990|" . count($conteudo) . "|";

        $filename = "bloco_k_" . now()->format('Ymd_His') . ".txt";
        Storage::put("public/sped/{$filename}", implode("\r\n", $conteudo));

        return $filename;
    }
}
