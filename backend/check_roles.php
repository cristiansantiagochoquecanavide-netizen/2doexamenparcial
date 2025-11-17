<?php
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

$roles = DB::table('carga_horaria.rol')
    ->select('id_rol', 'nombre')
    ->get();

echo "=== ROLES EN LA BASE DE DATOS ===\n";
foreach ($roles as $rol) {
    echo "ID: {$rol->id_rol} -> Nombre: {$rol->nombre}\n";
}

echo "\n=== USUARIO DOCENTE CI: 12377888 ===\n";
$usuario = DB::table('carga_horaria.usuario')
    ->join('carga_horaria.rol', 'usuario.id_rol', '=', 'rol.id_rol')
    ->where('usuario.ci_persona', '12377888')
    ->select('usuario.ci_persona', 'usuario.id_rol', 'rol.nombre as rol_nombre')
    ->first();

if ($usuario) {
    echo "CI: {$usuario->ci_persona}\n";
    echo "ID Rol: {$usuario->id_rol}\n";
    echo "Nombre Rol: {$usuario->rol_nombre}\n";
} else {
    echo "Usuario no encontrado\n";
}
?>
