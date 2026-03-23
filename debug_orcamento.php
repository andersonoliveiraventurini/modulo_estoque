<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$orc = App\Models\Orcamento::with(['itens.produto', 'consultaPrecoGrupo.itens.fornecedorSelecionado'])->find(145);
if ($orc) {
    echo json_encode($orc, JSON_PRETTY_PRINT);
} else {
    echo "Orcamento not found";
}
