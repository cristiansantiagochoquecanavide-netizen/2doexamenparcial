<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Simular una petición GET a /api/docentes
use App\Models\Docente;

$docentes = Docente::with(['usuario.persona', 'usuario.rol', 'asignaciones.grupo.materia'])->paginate(1000);

$data = $docentes->map(function ($docente) {
    $nombreCompleto = 'Desconocido';
    $ci = '';
    
    if ($docente->usuario) {
        if ($docente->usuario->persona) {
            $persona = $docente->usuario->persona;
            $nombreCompleto = trim("{$persona->nombre} {$persona->apellido_paterno} {$persona->apellido_materno}");
            $ci = $persona->ci;
        }
    }

    return [
        'codigo_doc' => $docente->codigo_doc,
        'ci' => $ci,
        'nombre_completo' => $nombreCompleto,
        'titulo' => $docente->titulo,
        'correo_institucional' => $docente->correo_institucional,
        'carga_horaria_max' => $docente->carga_horaria_max,
    ];
});

echo "=== RESPUESTA DEL API /docentes ===\n";
echo json_encode([
    'data' => $data->toArray(),
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?>
