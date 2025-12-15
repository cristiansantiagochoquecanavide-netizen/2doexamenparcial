<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Docente;

echo "=== Debug Estado Docente ===\n";
$docentes = Docente::with(['usuario.persona'])->limit(2)->get();
foreach ($docentes as $d) {
    $estado = $d->usuario->estado;
    echo "Docente: " . $d->codigo_doc . "\n";
    echo "  Estado (raw): " . var_export($estado, true) . "\n";
    echo "  Estado (type): " . gettype($estado) . "\n";
    echo "  Estado (boolean): " . ($estado ? 'true' : 'false') . "\n";
    echo "  CI Persona: " . ($d->usuario->persona ? $d->usuario->persona->ci : 'null') . "\n";
    echo "  Nombre Persona: " . ($d->usuario->persona ? $d->usuario->persona->nombre : 'null') . "\n";
    echo "\n";
}
