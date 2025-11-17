<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/bootstrap/app.php';

use Illuminate\Support\Facades\DB;

$usuarios = DB::table('usuario')
    ->join('rol', 'usuario.id_rol', '=', 'rol.id_rol')
    ->select('usuario.ci_persona', 'rol.nombre as rol_nombre')
    ->get();

echo "=== USUARIOS Y ROLES ===\n";
foreach ($usuarios as $u) {
    echo "CI: {$u->ci_persona} -> Rol: {$u->rol_nombre}\n";
}
?>
