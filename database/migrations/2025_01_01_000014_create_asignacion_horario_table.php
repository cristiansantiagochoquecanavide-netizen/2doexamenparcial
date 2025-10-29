<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Módulo: Planificación_Académica
     */
    public function up(): void
    {
        Schema::create('asignacion_horario', function (Blueprint $table) {
            $table->id('id_asignacion');
            $table->string('periodo_academico', 20);
            $table->string('estado', 20)->default('ACTIVO');
            $table->unsignedBigInteger('codigo_doc');
            $table->string('codigo_grupo', 20);
            $table->string('nro_aula', 20);
            $table->unsignedBigInteger('id_horario');
            
            $table->foreign('codigo_doc')
                ->references('codigo_doc')
                ->on('docente')
                ->onDelete('restrict');
                
            $table->foreign('codigo_grupo')
                ->references('codigo_grupo')
                ->on('grupo')
                ->onDelete('restrict');
                
            $table->foreign('nro_aula')
                ->references('nro_aula')
                ->on('aula')
                ->onDelete('restrict');
                
            $table->foreign('id_horario')
                ->references('id_horario')
                ->on('horario')
                ->onDelete('restrict');
        });
        
        // Índices únicos para evitar conflictos horarios
        Schema::table('asignacion_horario', function (Blueprint $table) {
            $table->unique(['codigo_doc', 'id_horario', 'periodo_academico'], 'ux_docente_horario_periodo');
            $table->unique(['codigo_grupo', 'id_horario', 'periodo_academico'], 'ux_grupo_horario_periodo');
            $table->unique(['nro_aula', 'id_horario', 'periodo_academico'], 'ux_aula_horario_periodo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asignacion_horario');
    }
};
