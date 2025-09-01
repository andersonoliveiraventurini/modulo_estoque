<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ClienteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // carga de clientes inicial
        /*
        numero -> idbrcom

     */

        /*valor para analise de limite de credito 
        
       tabela brcom - 
        referencias,
        filiação,
        referencias2,
        ultima,
        referencias3,

        */



    /* Após dar a carga do cliente, rodar o comando abaixo para ajustar os campos de endereço
     tabela brcom* 
    nome,
    endereço,
    bairro,
    cidade,
    cep,

    sendo  tabela endereços
            logradouro,
            numero,
            complemento,
            bairro,
            cidade,
            estado,
            cep,
            cliente_id
     */

    /* Após dar a carga do cliente, rodar o comando abaixo para ajustar os campos de telefone
     * 
     *tabela brcom*  
    telefone_res,
    telefone_cial,
    celular,
    email,

    sendo tabela de contatos
        nome,
        email,
        telefone,
        cliente_id
     */



        /* campos descartados
         * 
         * relaciona a tabela de clientes 2 - campos cliente     empresa,

valores nulos, 1 ou 0 :
valor,
    funcionario,

    ref_cial,
    emi_rg,
    carta,
    seproc,
    idcarta,
cliente,
    complemento,
    fornecedor,
    compl,
    arquivo,
    vencimento,
    enviado,
    nascimento, carteira,
    cheque,
    boleto,
    cadastro,
avisar,
    cf,
         */
    }
}
