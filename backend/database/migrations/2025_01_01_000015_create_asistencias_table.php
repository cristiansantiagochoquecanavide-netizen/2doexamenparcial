<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Módulo: Asistencia_Docente
     */
    public function up(): void
    {
        Schema::create('asistencias', function (Blueprint $table) {
            $table->id('id_asistencias');
            $table->date('fecha');
            $table->time('hora_de_registro')->useCurrent();
            $table->string('tipo_registro', 20)->nullable();
            $table->string('estado', 20)->nullable();
            $table->unsignedBigInteger('id_asignacion');
            
            $table->foreign('id_asignacion')
                ->references('id_asignacion')
                ->on('asignacion_horario')
                ->onDelete('cascade');
        });
        
        // Índice único para asegurar una sola asistencia por día y asignación
        Schema::table('asistencias', function (Blueprint $table) {
            $table->unique(['id_asignacion', 'fecha'], 'ux_asistencia_unica');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asistencias');
    }
};
