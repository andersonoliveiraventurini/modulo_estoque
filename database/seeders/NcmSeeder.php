<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NcmSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
       $path = database_path('seeders/_carga_inicial/ncm_vigente_20250825.json');
        $json = json_decode(file_get_contents($path), true);

        $nomenclaturas = $json['Nomenclaturas'];

        $batch = [];
        $chunkSize = 1000;

        foreach ($nomenclaturas as $item) {
            $batch[] = [
                'codigo'      => $item['Codigo'],
                'descricao'   => $item['Descricao'],
                'data_inicio' => !empty($item['Data_Inicio']) ? date('Y-m-d', strtotime($item['Data_Inicio'])) : null,
                'data_fim'    => !empty($item['Data_Fim']) ? date('Y-m-d', strtotime($item['Data_Fim'])) : null,
                'ato_legal'   => $item['Tipo_Ato_Ini'] ?? null,
                'numero'      => $item['Numero_Ato_Ini'] ?? null,
                'ano'         => $item['Ano_Ato_Ini'] ?? null,
                'created_at'  => now(),
                'updated_at'  => now(),
            ];

            if (count($batch) >= $chunkSize) {
                DB::table('ncms')->insert($batch);
                $batch = [];
            }
        }

        // Insere o que sobrou
        if (!empty($batch)) {
            DB::table('ncms')->insert($batch);
        }

    }
}
