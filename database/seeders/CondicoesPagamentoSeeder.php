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
            'Boleto bancário',
            'Cheque',
            'Cartão de crédito',
            'Cartão de débito',
            'Carteira',
            'Dinheiro',
        ];

        foreach ($tipos as $tipo) {
            CondicoesPagamento::updateOrCreate(['nome' => $tipo]);
        }
    }
}
