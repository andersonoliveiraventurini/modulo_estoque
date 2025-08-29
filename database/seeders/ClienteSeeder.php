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
        /**
    numero,
    referencias,
    cpf,
    rg,
    cnpj,
    usuario,
    cliente,
    fornecedor,
    nascimento,
    contato,
    vencimento,
    valor,
    carta,
    seproc,
    idcarta,
    enviado,
    bloqueio,
    ref_cial,
    emi_rg,
    filiação,
    funcionario,
    empresa,
    fantasia,
    codmun,
    casa,
    UF,
    avisar,
    complemento,
    limite,
    vendedor,
    compl,
    momento,
    referencias2,
    ultima,
    referencias3,
    entrega,
    arquivo,
    externo,
    carteira,
    cheque,
    boleto,
    cadastro,
    venc_limite,
    desconto,
    cf,
    tratamento,
     */


    /** Após dar a carga do cliente, rodar o comando abaixo para ajustar os campos de endereço
     * 
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

    /** Após dar a carga do cliente, rodar o comando abaixo para ajustar os campos de telefone
     * 
     * 
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
    }
}
