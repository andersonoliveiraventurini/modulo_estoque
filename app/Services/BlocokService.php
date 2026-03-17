<?php

namespace App\Services;

use App\Models\Produto;
use App\Models\Blocok;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class BlocokService
{
    /**
     * Gera os registros do Bloco K para um período específico salvando direto no arquivo.
     */
    public function gerarRegistrosParaPeriodo($dataInicio, $dataFim)
    {
        // Criação inicial do registro no banco com os identificadores básicos
        $bloco = Blocok::create([
            'k001' => '0',
            'k100' => $dataInicio->format('dmY') . '|' . $dataFim->format('dmY'),
            // k200 não será mais salvo no banco devido ao limite de string
            'k990' => 'Total de registros em processamento', // Será atualizado depois
        ]);

        return $bloco;
    }

    /**
     * Registro 0200: Tabela de Identificação do Item
     */
    public function gerarRegistro0200()
    {
        $produtos = Produto::all();
        $linhas = [];

        foreach ($produtos as $produto) {
            $linha = [
                '0200',
                $produto->sku,
                $this->limparTexto($produto->nome),
                '', // COD_BARRA
                '', // COD_ANT_ITEM
                'UN', // UNID_INV
                '04', // TIPO_ITEM
                $produto->ncm?->codigo ?? '00000000',
                '', '', ''
            ];
            $linhas[] = '|' . implode('|', $linha) . '|';
        }

        return $linhas;
    }

    /**
     * Registro K200: Estoque Escriturado
     */
    public function gerarRegistroK200($dataReferencia)
    {
        $produtos = Produto::where('estoque_atual', '>', 0)->get();
        $linhas = [];

        foreach ($produtos as $produto) {
            $linha = [
                'K200',
                $dataReferencia->format('dmY'),
                $produto->sku,
                number_format($produto->estoque_atual, 3, '.', ''),
                '0', // IND_EST
                ''   // COD_PART
            ];
            $linhas[] = '|' . implode('|', $linha) . '|';
        }

        return $linhas;
    }

    private function limparTexto($texto)
    {
        return str_replace(['|', "\n", "\r"], [' ', '', ''], $texto);
    }

    /**
     * Exporta o arquivo TXT completo do Bloco K e salva o caminho no banco.
     */
    public function exportarTxt(Blocok $bloco)
    {
        $conteudo = [];
        $conteudo[] = "|K001|{$bloco->k001}|";
        $conteudo[] = "|K100|{$bloco->k100}|";
        
        // Pega as datas do K100 guardado no banco
        $datas = explode('|', $bloco->k100);
        $dataFim = \Carbon\Carbon::createFromFormat('dmY', $datas[1]);

        // Gerar linhas e adicionar ao array de conteúdo (evitando array para string conversion direta)
        $linhas0200 = $this->gerarRegistro0200();
        $conteudo = array_merge($conteudo, $linhas0200);
        
        $linhasK200 = $this->gerarRegistroK200($dataFim);
        $conteudo = array_merge($conteudo, $linhasK200);
        
        $totalLinhas = count($conteudo) + 1; // +1 pela linha k990 que será adicionada
        $conteudo[] = "|K990|{$totalLinhas}|";

        $filename = "bloco_k_" . now()->format('Ymd_His') . ".txt";
        
        if (!Storage::exists('public/sped')) {
            Storage::makeDirectory('public/sped');
        }

        Storage::put("public/sped/{$filename}", implode("\r\n", $conteudo));

        // Atualizar o registro no banco de dados com o caminho do arquivo e o K990 correto
        $bloco->update([
            'k990' => $totalLinhas,
            'arquivo_path' => "public/sped/{$filename}",
            'k200' => null // Limpa para garantir
        ]);

        return $filename;
    }
}
