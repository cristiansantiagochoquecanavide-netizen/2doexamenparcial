<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('malla_horaria', function (Blueprint $table) {
            $table->id('id_malla');
            $table->integer('gestion')->unique(); // Ej: 2024, 2025
            $table->integer('cantidad_turnos'); // Cantidad de turnos por día
            $table->integer('duracion_bloque_minutos'); // Duración de cada bloque en minutos
            $table->string('estado', 30)->default('Activo'); // Activo, Inactivo
            $table->timestamp('fecha_creacion')->useCurrent();
            $table->timestamp('fecha_modificacion')->useCurrent()->useCurrentOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('malla_horaria');
    }
};
