<?php
require 'vendor/autoload.php';

// Cargar Laravel
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

// Buscar el docente "Ubaldo Perez"
$usuario = DB::table('usuario')
    ->join('persona', 'usuario.ci_persona', '=', 'persona.ci')
    ->select('usuario.*', 'persona.nombre')
    ->where('persona.nombre', 'Ubaldo Perez')
    ->first();

if ($usuario) {
    echo "Usuario encontrado:\n";
    echo "ID: {$usuario->id_usuario}\n";
    echo "CI: {$usuario->ci_persona}\n";
    echo "Nombre: {$usuario->nombre}\n";
    echo "Estado (DB value): {$usuario->estado}\n";
    echo "Estado (type): " . gettype($usuario->estado) . "\n";
    echo "Estado == true: " . var_export($usuario->estado == true, true) . "\n";
    echo "Estado === true: " . var_export($usuario->estado === true, true) . "\n";
    echo "Estado == 1: " . var_export($usuario->estado == 1, true) . "\n";
    echo "Estado === 1: " . var_export($usuario->estado === 1, true) . "\n";
} else {
    echo "Usuario no encontrado\n";
}
?>
