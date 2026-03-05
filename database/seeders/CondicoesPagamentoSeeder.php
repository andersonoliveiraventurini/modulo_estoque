<?php

namespace Database\Seeders;

use App\Models\CondicoesPagamento;
use Illuminate\Database\Seeder;

class CondicoesPagamentoSeeder extends Seeder
{
    public function run(): void
    {
        $condicoes = [
            ['nome' => 'PIX',                  'tipo' => 'pix',             'ordem' => 1],
            ['nome' => 'Dinheiro',             'tipo' => 'dinheiro',        'ordem' => 2],
            ['nome' => 'Cartão de crédito',    'tipo' => 'cartao_credito',  'ordem' => 3],
            ['nome' => 'Cartão de débito',     'tipo' => 'cartao_debito',   'ordem' => 4],
            ['nome' => 'Carteira',             'tipo' => 'credito_cliente', 'ordem' => 5],
            ['nome' => 'Boleto 07 dias',       'tipo' => 'boleto',          'ordem' => 6],
            ['nome' => 'Boleto 14 dias',       'tipo' => 'boleto',          'ordem' => 7],
            ['nome' => 'Boleto 21 dias',       'tipo' => 'boleto',          'ordem' => 8],
            ['nome' => 'Boleto 28 dias',       'tipo' => 'boleto',          'ordem' => 9],
            ['nome' => 'Boleto 28/56 dias',    'tipo' => 'boleto',          'ordem' => 10],
            ['nome' => 'Boleto 28/42/56 dias', 'tipo' => 'boleto',          'ordem' => 11],
            ['nome' => 'Boleto 28/56/84 dias', 'tipo' => 'boleto',          'ordem' => 12],
            ['nome' => 'Cheque 07 dias',       'tipo' => 'cheque',          'ordem' => 13],
            ['nome' => 'Cheque 14 dias',       'tipo' => 'cheque',          'ordem' => 14],
            ['nome' => 'Cheque 21 dias',       'tipo' => 'cheque',          'ordem' => 15],
            ['nome' => 'Cheque 28 dias',       'tipo' => 'cheque',          'ordem' => 16],
            ['nome' => 'Cheque 28/56 dias',    'tipo' => 'cheque',          'ordem' => 17],
            ['nome' => 'Cheque 28/42/56 dias', 'tipo' => 'cheque',          'ordem' => 18],
            ['nome' => 'Cheque 28/56/84 dias', 'tipo' => 'cheque',          'ordem' => 19],
            ['nome' => 'Outros',               'tipo' => 'outros',          'ordem' => 20],
        ];

        foreach ($condicoes as $dados) {
            CondicoesPagamento::updateOrCreate(
                ['nome' => $dados['nome']],
                [
                    'tipo'  => $dados['tipo'],
                    'ordem' => $dados['ordem'],
                    'ativo' => true,
                ]
            );
        }
    }
}