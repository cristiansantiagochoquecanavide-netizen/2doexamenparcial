<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsuarioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        // Crear el rol solo si no existe
        $rolId = DB::table('rol')->where('nombre', 'Administrador')->value('id_rol');
        if (!$rolId) {
            $rolId = DB::table('rol')->insertGetId([
                'nombre' => 'Administrador',
                'descripcion' => 'Rol de administrador del sistema'
            ], 'id_rol');
        }

        // Eliminar usuarios y personas duplicadas
        DB::table('usuario')->whereIn('ci_persona', ['12345678', 'superadmin'])->delete();
        DB::table('persona')->whereIn('ci', ['12345678', 'superadmin'])->delete();

        // Crear la persona admin
        DB::table('persona')->insert([
            'ci' => '12345678',
            'nombre' => 'Admin Sistema',
            'telefono' => '70000000',
            'email' => 'admin@sistema.com',
            'direccion' => 'La Paz, Bolivia'
        ]);

        // Crear el usuario admin
        DB::table('usuario')->insert([
            'ci_persona' => '12345678',
            'id_rol' => $rolId,
            'contrasena' => Hash::make('12345678'),
            'estado' => true
        ]);

        // Crear la persona superadmin
        DB::table('persona')->insert([
            'ci' => 'superadmin',
            'nombre' => 'Super Administrador',
            'telefono' => '79999999',
            'email' => 'superadmin@sistema.com',
            'direccion' => 'Oficina Central'
        ]);

        // Crear el usuario superadmin
        DB::table('usuario')->insert([
            'ci_persona' => 'superadmin',
            'id_rol' => $rolId,
            'contrasena' => Hash::make('superadmin'),
            'estado' => true
        ]);

        $this->command->info('Usuario de prueba creado exitosamente!');
        $this->command->info('CI: 12345678 | Contraseña: 12345678');
        $this->command->info('Superusuario creado!');
        $this->command->info('CI: superadmin | Contraseña: superadmin');
    }
}
