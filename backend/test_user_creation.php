<?php
/**
 * Script para probar la creación de usuario directamente
 * Úsalo con: php test_user_creation.php
 */

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Persona;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

echo "🧪 Test de Creación de Usuario\n";
echo "=" . str_repeat("=", 50) . "\n\n";

try {
    // Verificar conexión a BD
    echo "1️⃣ Verificando conexión a base de datos...\n";
    DB::connection()->getPdo();
    echo "   ✅ Conexión establecida\n\n";

    // Mostrar estado actual
    echo "2️⃣ Estado actual de las tablas:\n";
    echo "   Personas: " . Persona::count() . "\n";
    echo "   Roles: " . Rol::count() . "\n";
    echo "   Usuarios: " . Usuario::count() . "\n\n";

    // Verificar si ya existe
    echo "3️⃣ Verificando si usuario ya existe...\n";
    $usuarioExistente = Usuario::where('ci_persona', '12345678')->first();
    
    if ($usuarioExistente) {
        echo "   ✅ Usuario ya existe:\n";
        echo "      CI: " . $usuarioExistente->ci_persona . "\n";
        echo "      Estado: " . ($usuarioExistente->estado ? 'Activo' : 'Inactivo') . "\n";
        echo "      Rol: " . ($usuarioExistente->id_rol ? 'Asignado' : 'Sin rol') . "\n";
    } else {
        echo "   ⚠️ Usuario no existe, creando...\n\n";

        // Crear persona
        echo "4️⃣ Creando Persona...\n";
        $persona = Persona::create([
            'ci' => '12345678',
            'nombre' => 'Usuario',
            'apellido' => 'Test',
            'email' => 'test@example.com',
            'telefono' => '12345678'
        ]);
        echo "   ✅ Persona creada con ID: " . $persona->id_persona . "\n\n";

        // Crear o obtener rol
        echo "5️⃣ Obteniendo/creando Rol Administrador...\n";
        $rol = Rol::where('nombre', 'Administrador')->first();
        
        if (!$rol) {
            $rol = Rol::create([
                'nombre' => 'Administrador',
                'descripcion' => 'Administrador del sistema',
                'estado' => true
            ]);
            echo "   ✅ Rol creado con ID: " . $rol->id_rol . "\n";
        } else {
            echo "   ✅ Rol encontrado con ID: " . $rol->id_rol . "\n";
        }
        echo "\n";

        // Crear usuario
        echo "6️⃣ Creando Usuario...\n";
        $usuario = Usuario::create([
            'ci_persona' => '12345678',
            'contrasena' => Hash::make('12345678'),
            'estado' => true,
            'id_rol' => $rol->id_rol
        ]);
        echo "   ✅ Usuario creado exitosamente\n";
        echo "      ID: " . $usuario->id_usuario . "\n";
        echo "      CI: " . $usuario->ci_persona . "\n";
        echo "      Estado: " . ($usuario->estado ? 'Activo' : 'Inactivo') . "\n\n";
    }

    // Verificar credenciales
    echo "7️⃣ Verificando credenciales...\n";
    $usuarioLogin = Usuario::with(['persona', 'rol.permisos'])
        ->where('ci_persona', '12345678')
        ->where('estado', true)
        ->first();

    if ($usuarioLogin && Hash::check('12345678', $usuarioLogin->contrasena)) {
        echo "   ✅ Credenciales válidas\n";
        echo "      Puede iniciar sesión correctamente\n\n";
    } else {
        echo "   ❌ Las credenciales NO son válidas\n\n";
    }

    echo "=" . str_repeat("=", 50) . "\n";
    echo "✅ Test completado exitosamente\n";

} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "   Archivo: " . $e->getFile() . "\n";
    echo "   Línea: " . $e->getLine() . "\n";
}
?>
