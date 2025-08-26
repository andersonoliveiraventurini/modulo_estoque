<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class FornecedorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         // Leitura do arquivo CSV da storage
        $path = database_path('seeders/_carga_inicial/linhas.csv');

        if (!file_exists($path) || !is_readable($path)) {
            $this->command->error('Arquivo CSV não encontrado ou não pode ser lido.');
            return;
        }

        $header = null;
        $data = [];

        if (($handle = fopen($path, 'r')) !== false) {
            while (($row = fgetcsv($handle, 10000, ',')) !== false) {
                if (!$header) {
                    $header = $row;
                } else {
                    $linha = $row[array_search('linha', $header)];
                    $descricao = $row[array_search('descrição', $header)];
                    $desc = $row[array_search('descricao', $header)];
                    $obs = $row[array_search('obs', $header)];

                    $data[] = [
                        'linha_brcom' => $linha,
                        'nome_fantasia' => $descricao,
                        'descricao' => $desc,
                        'observacao' => $obs,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
            fclose($handle);
        }

        // Inserir no banco (supondo que a tabela seja 'fornecedores')
        DB::table('fornecedores')->insert($data);

        $this->command->info('Fornecedores importados com sucesso!');
    }
}
