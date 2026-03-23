<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';

use App\Http\Controllers\OrcamentoController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

// Mock login (vendedor #1 or similar)
Auth::loginUsingId(1);

$request = new Request(['status' => 'Aprovado']);
$request->setMethod('PUT');

$controller = app(OrcamentoController::class);
$response = $controller->atualizarStatus($request, 145);

echo "Status: " . $response->status() . "\n";
echo "Body: " . $response->getContent() . "\n";
echo "Final Budget Status: " . \App\Models\Orcamento::find(145)->status . "\n";
