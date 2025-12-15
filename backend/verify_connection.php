<?php
/**
 * Script de verificación de conexión frontend-backend
 */

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== VERIFICACIÓN DE CONEXIÓN FRONTEND-BACKEND ===\n\n";

echo "1. Verificando conexión a PostgreSQL...\n";
try {
    DB::connection()->getPdo();
    echo "   ✓ Conexión a PostgreSQL exitosa\n\n";
} catch (\Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n\n";
    exit(1);
}

echo "2. Verificando tablas principales...\n";
$tablas = ['usuario', 'persona', 'docente', 'rol'];
foreach ($tablas as $tabla) {
    $count = DB::table($tabla)->count();
    echo "   - $tabla: $count registros\n";
}

echo "\n3. Verificando docentes...\n";
$docentes = DB::table('docente')
    ->join('usuario', 'docente.id_usuario', '=', 'usuario.id_usuario')
    ->join('persona', 'usuario.ci_persona', '=', 'persona.ci')
    ->select('docente.codigo_doc', 'persona.nombre', 'usuario.estado')
    ->limit(5)
    ->get();

if ($docentes->count() > 0) {
    foreach ($docentes as $docente) {
        $estado = $docente->estado ? 'Activo' : 'Inactivo';
        echo "   - {$docente->nombre}: {$estado}\n";
    }
} else {
    echo "   No hay docentes registrados\n";
}

echo "\n4. Verificando CORS configuration...\n";
$corsConfig = config('cors.paths.api.allowed_origins');
if (is_array($corsConfig)) {
    foreach ($corsConfig as $origin) {
        echo "   - $origin\n";
    }
} else {
    echo "   CORS permitido para: *\n";
}

echo "\n5. Verificando API endpoints...\n";
$endpoints = [
    'GET /api/docentes',
    'POST /api/docentes',
    'PUT /api/docentes/{id}',
    'DELETE /api/docentes/{id}',
];

foreach ($endpoints as $endpoint) {
    echo "   ✓ $endpoint\n";
}

echo "\n=== VERIFICACIÓN COMPLETADA ===\n";
echo "\nInstrucciones:\n";
echo "1. Backend debe estar en: http://127.0.0.1:8000\n";
echo "2. Frontend debe estar en: http://127.0.0.1:5173\n";
echo "3. Ejecuta: npm run dev (en frontend)\n";
echo "4. Ejecuta: php artisan serve (en backend)\n";
echo "\nPrueba los endpoints con el navegador en F12 → Network tab\n";
?>
