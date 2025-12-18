<?php

namespace Database\Seeders;

use App\Models\Usuario;
use App\Models\Persona;
use App\Models\Rol;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsuarioTestSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        try {
            // Verificar si el usuario ya existe
            $usuarioExistente = Usuario::where('ci_persona', '12345678')->first();
            if ($usuarioExistente) {
                echo "✅ Usuario de prueba ya existe\n";
                return;
            }

            // Crear persona
            $persona = Persona::create([
                'ci' => '12345678',
                'nombre' => 'Usuario Test',
                'email' => 'test@example.com',
                'telefono' => '12345678'
            ]);

            // Obtener o crear rol Administrador
            $rol = Rol::where('nombre', 'Administrador')->first();
            if (!$rol) {
                $rol = Rol::create([
                    'nombre' => 'Administrador',
                    'descripcion' => 'Administrador del sistema'
                ]);
            }

            // Crear usuario (nota: la relación es a través de ci_persona, NO id_persona)
            $usuario = Usuario::create([
                'ci_persona' => '12345678',  // Referencia a persona.ci
                'contrasena' => Hash::make('12345678'),
                'estado' => true,
                'id_rol' => $rol->id_rol
            ]);

            echo "✅ Usuario de prueba creado exitosamente\n";
            echo "   CI: 12345678\n";
            echo "   Contraseña: 12345678\n";
        } catch (\Exception $e) {
            echo "❌ Error al crear usuario: " . $e->getMessage() . "\n";
        }
    }
}
