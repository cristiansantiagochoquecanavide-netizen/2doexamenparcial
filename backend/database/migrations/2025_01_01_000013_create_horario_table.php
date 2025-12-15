<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Módulo: Planificación_Académica
     */
    public function up(): void
    {
        Schema::create('horario', function (Blueprint $table) {
            $table->id('id_horario');
            $table->string('dias_semana', 20);
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->string('turno', 20)->nullable();
        });

        // Agregar constraint CHECK sin schema explícito para compatibilidad multiplataforma
        DB::statement('ALTER TABLE horario ADD CONSTRAINT chk_horario_valido CHECK (hora_fin > hora_inicio)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('horario');
    }
};
