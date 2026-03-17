<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$id = 22;
$orcamento = App\Models\Orcamento::find($id);
if (!$orcamento) {
    echo "Orcamento $id not found.\n";
    exit;
}

echo "Status: " . $orcamento->status . "\n";
echo "Workflow Status: " . $orcamento->workflow_status . "\n";
echo "Transportes: \n";
foreach ($orcamento->transportes as $t) {
    echo "- ID: {$t->id}, Tipo: {$t->pivot->tipo_transporte_id}\n";
}

$routeIds = [1, 2, 3, 6, 7];
$hasRoute = $orcamento->transportes()->whereIn('tipo_transporte_id', $routeIds)->exists();
echo "Has Route (1,2,3,6,7)? " . ($hasRoute ? 'Yes' : 'No') . "\n";
