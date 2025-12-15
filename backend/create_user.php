<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Persona;
use App\Models\Usuario;
use App\Models\Rol;
use Illuminate\Support\Facades\Hash;

try {
    // Datos del nuevo usuario
    $ci = '12345678';
    $nombres = 'Usuario';
    $apellidos = 'Prueba';
    $email = 'usuario@test.com';
    $password = 'password123';
    $rol_nombre = 'Administrador'; // Puedes cambiar esto

    // Crear o actualizar la persona
    $persona = Persona::updateOrCreate(
        ['ci' => $ci],
        [
            'nombre' => "$nombres $apellidos",
            'email' => $email,
        ]
    );

    echo "✓ Persona creada/actualizada: CI=$ci\n";

    // Obtener el rol
    $rol = Rol::where('nombre', $rol_nombre)->first();
    if (!$rol) {
        // Crear el rol si no existe
        echo "⚠ Rol '$rol_nombre' no existe. Creando...\n";
        $rol = Rol::create([
            'nombre' => $rol_nombre,
            'descripcion' => "Rol de $rol_nombre creado automáticamente",
        ]);
        echo "✓ Rol creado: $rol_nombre\n";
    }

    // Crear o actualizar el usuario
    $usuario = Usuario::updateOrCreate(
        ['ci_persona' => $ci],
        [
            'login' => $ci,
            'contrasena' => Hash::make($password),
            'id_rol' => $rol->id_rol,
            'estado' => true,
        ]
    );

    echo "✓ Usuario creado/actualizado: CI=$ci\n";
    echo "\n═══════════════════════════════════════\n";
    echo "Datos para iniciar sesión:\n";
    echo "═══════════════════════════════════════\n";
    echo "CI (Cédula):    $ci\n";
    echo "Contraseña:     $password\n";
    echo "Rol:            $rol_nombre\n";
    echo "═══════════════════════════════════════\n";

} catch (\Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
