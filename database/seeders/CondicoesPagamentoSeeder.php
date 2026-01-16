<?php

namespace Database\Seeders;

use App\Models\CondicoesPagamento;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CondicoesPagamentoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tipos = [
            'PIX',
            'Carteira',
            'Dinheiro',
            'Cartão de crédito',
            'Cartão de débito',
            'Boleto 07 dias',
            'Boleto 14 dias',
            'Boleto 21 dias',
            'Boleto 28 dias',
            'Boleto 28/56 dias',
            'Boleto 28/42/56 dias',
            'Boleto 28/56/84 dias',
            'Cheque 07 dias',
            'Cheque 14 dias',
            'Cheque 21 dias',
            'Cheque 28 dias',
            'Cheque 28/56 dias',
            'Cheque 28/42/56 dias',
            'Cheque 28/56/84 dias',
            'Outros'
        ];

        foreach ($tipos as $tipo) {
            CondicoesPagamento::updateOrCreate(['nome' => $tipo]);
        }
    }
}
