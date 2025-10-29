<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Crear el schema carga_horaria si no existe
        DB::statement('CREATE SCHEMA IF NOT EXISTS carga_horaria');
        
        // Establecer el search_path
        DB::statement('SET search_path TO carga_horaria');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No eliminar el schema en el rollback por seguridad
        // Si se desea eliminar: DB::statement('DROP SCHEMA IF EXISTS carga_horaria CASCADE');
    }
};
