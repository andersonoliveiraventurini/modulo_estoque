<?php

namespace Database\Seeders;

use App\Models\FormaPagamento;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FormaPagamentoSeeder extends Seeder
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
            FormaPagamento::updateOrCreate(['nome' => $tipo]);
        }
    }
}
