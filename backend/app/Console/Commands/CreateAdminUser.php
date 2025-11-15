<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Persona;
use App\Models\Usuario;
use App\Models\Rol;
use Illuminate\Support\Facades\Hash;

class CreateAdminUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-admin-user {ci? : CI del usuario} {nombre? : Nombre del usuario} {email? : Email del usuario}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create an admin user for testing';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $ci = $this->argument('ci') ?? '12345678';
        $nombre = $this->argument('nombre') ?? 'Admin User';
        $email = $this->argument('email') ?? 'admin@test.com';
        $password = '12345678';

        try {
            // Crear o actualizar persona (tabla persona, columna primaria: ci)
            $persona = Persona::firstOrCreate(
                ['ci' => $ci],
                [
                    'nombre' => $nombre,
                    'email' => $email,
                ]
            );

            $this->info("✓ Persona: $nombre (CI: $ci)");

            // Obtener el rol de Administrador
            $rol_admin = Rol::where('nombre', 'Administrador')->first();
            if (!$rol_admin) {
                $this->error("✗ El rol 'Administrador' no existe en la base de datos");
                return 1;
            }

            // Crear o actualizar usuario (tabla usuario, columna: ci_persona)
            $usuario = Usuario::firstOrCreate(
                ['ci_persona' => $ci],
                [
                    'id_rol' => $rol_admin->id_rol,
                    'contrasena' => Hash::make($password),
                    'estado' => true,
                ]
            );

            $this->info("✓ Usuario creado: $ci");
            $this->info("  - Rol: Administrador");
            $this->info("  - Contraseña: $password");
            $this->info("\n✓ Admin user created successfully");
            return 0;
        } catch (\Exception $e) {
            $this->error("✗ Error: {$e->getMessage()}");
            return 1;
        }
    }
}
