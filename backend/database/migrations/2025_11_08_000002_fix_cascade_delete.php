<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Eliminar la restricción existente
        DB::statement('ALTER TABLE carga_horaria.usuario DROP CONSTRAINT IF EXISTS usuario_ci_persona_foreign CASCADE');
        
        // Recrear la restricción con DELETE CASCADE
        DB::statement('ALTER TABLE carga_horaria.usuario ADD CONSTRAINT usuario_ci_persona_foreign 
            FOREIGN KEY (ci_persona) REFERENCES carga_horaria.persona(ci) ON DELETE CASCADE');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Si es necesario revertir
        DB::statement('ALTER TABLE carga_horaria.usuario DROP CONSTRAINT IF EXISTS usuario_ci_persona_foreign');
        DB::statement('ALTER TABLE carga_horaria.usuario ADD CONSTRAINT usuario_ci_persona_foreign 
            FOREIGN KEY (ci_persona) REFERENCES carga_horaria.persona(ci)');
    }
};
