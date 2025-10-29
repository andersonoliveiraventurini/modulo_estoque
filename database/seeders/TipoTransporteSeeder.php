<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\TipoTransporte;

class TipoTransporteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tipos = [
            'Tavares',
            'Transportadora',
            'Sedex',
            'Balcão',
            'Retira WhatsApp',
            'Rota',
            'Express',
        ];

        foreach ($tipos as $tipo) {
            TipoTransporte::updateOrCreate(['nome' => $tipo]);
        }
    }
}
