<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Docente;

// Obtener un docente con sus relaciones
$docente = Docente::with('usuario.persona')->first();

if ($docente) {
    echo "=== DOCENTE COMPLETO ===\n";
    echo json_encode([
        'codigo_doc' => $docente->codigo_doc,
        'ci' => $docente->ci ?? 'NO DISPONIBLE',
        'usuario_ci_persona' => $docente->usuario->ci_persona ?? 'NO DISPONIBLE',
        'persona_ci' => $docente->usuario->persona->ci ?? 'NO DISPONIBLE',
        'nombre' => $docente->usuario->persona->nombre ?? 'NO DISPONIBLE',
        'titulo' => $docente->titulo,
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
} else {
    echo "No hay docentes\n";
}
?>
