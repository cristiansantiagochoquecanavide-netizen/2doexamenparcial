<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Usuario;
use App\Models\Persona;
use App\Models\Rol;
use Illuminate\Support\Facades\Hash;

class InitializeApp extends Command
{
    protected $signature = 'app:initialize';
    protected $description = 'Inicializar la aplicación: migraciones, seeder y usuario de prueba';

    public function handle()
    {
        $this->info('🚀 Inicializando aplicación...');

        try {
            // Ejecutar migraciones
            $this->info('📦 Ejecutando migraciones...');
            $this->call('migrate', ['--force' => true, '--no-interaction' => true]);
            $this->info('✅ Migraciones completadas');

            // Crear usuario de prueba
            $this->info('👥 Creando usuario de prueba...');
            $this->createTestUser();
            $this->info('✅ Usuario de prueba creado/verificado');

            // Cachear
            $this->info('⚙️  Cacheando configuración...');
            $this->call('config:cache');
            $this->call('route:cache');
            $this->call('view:cache');
            $this->info('✅ Cache actualizado');

            $this->info('✅ Inicialización completada exitosamente');
            return 0;
        } catch (\Exception $e) {
            $this->error('❌ Error en inicialización: ' . $e->getMessage());
            return 1;
        }
    }

    private function createTestUser()
    {
        try {
            // Verificar si ya existe
            $usuarioExistente = Usuario::where('ci_persona', '12345678')->first();
            if ($usuarioExistente) {
                $this->info('   ✅ Usuario ya existe (ID: ' . $usuarioExistente->id_usuario . ')');
                return;
            }

            // Crear persona
            $persona = Persona::firstOrCreate(
                ['ci' => '12345678'],
                [
                    'nombre' => 'Usuario',
                    'apellido' => 'Test',
                    'email' => 'test@example.com',
                    'telefono' => '12345678'
                ]
            );
            $this->info('   ✅ Persona creada (ID: ' . $persona->id_persona . ')');

            // Crear/obtener rol
            $rol = Rol::where('nombre', 'Administrador')->first();
            if (!$rol) {
                $rol = Rol::create([
                    'nombre' => 'Administrador',
                    'descripcion' => 'Administrador del sistema',
                    'estado' => true
                ]);
                $this->info('   ✅ Rol creado (ID: ' . $rol->id_rol . ')');
            } else {
                $this->info('   ✅ Rol encontrado (ID: ' . $rol->id_rol . ')');
            }

            // Crear usuario
            $usuario = Usuario::create([
                'ci_persona' => '12345678',
                'contrasena' => Hash::make('12345678'),
                'estado' => true,
                'id_rol' => $rol->id_rol
            ]);

            $this->info('   ✅ Usuario creado (ID: ' . $usuario->id_usuario . ')');
            $this->info('   📝 Credenciales: CI=12345678 | Contraseña=12345678');

        } catch (\Exception $e) {
            $this->error('   ❌ Error creando usuario: ' . $e->getMessage());
            throw $e;
        }
    }
}
