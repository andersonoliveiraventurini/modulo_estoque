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
            'Boleto 07 dias',
            'Boleto 14 dias',
            'Boleto 21 dias',
            'Boleto 28 dias',
            'Boleto 28/56 dias',
            'Boleto 28/42/56 dias',
            'Boleto 28/56/84 dias',
            'Cheque',
            'Cartão de crédito',
            'Cartão de débito',
            'Carteira',
            'Dinheiro',
            'Outros'
        ];

        foreach ($tipos as $tipo) {
            CondicoesPagamento::updateOrCreate(['nome' => $tipo]);
        }
    }
}
