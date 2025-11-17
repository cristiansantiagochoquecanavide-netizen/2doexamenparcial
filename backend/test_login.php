<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Verificando usuario 12345678...\n";

try {
    $usuario = \App\Models\Usuario::with(['persona', 'rol.permisos'])
        ->where('ci_persona', '12345678')
        ->where('estado', true)
        ->first();
    
    if ($usuario) {
        echo "✅ Usuario encontrado\n";
        echo "CI: " . $usuario->ci_persona . "\n";
        echo "Estado: " . ($usuario->estado ? 'activo' : 'inactivo') . "\n";
        echo "Rol: " . $usuario->rol->nombre . "\n";
        echo "Persona: " . $usuario->persona->nombre . "\n";
        
        // Verificar contraseña
        $password = '12345678';
        $check = \Illuminate\Support\Facades\Hash::check($password, $usuario->contrasena);
        echo "Password check: " . ($check ? '✅ CORRECTO' : '❌ INCORRECTO') . "\n";
    } else {
        echo "❌ Usuario NO encontrado\n";
    }
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
