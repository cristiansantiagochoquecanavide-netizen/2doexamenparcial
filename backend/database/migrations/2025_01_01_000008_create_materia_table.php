<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Módulo: Gestión_de_Catálogos_Académicos
     */
    public function up(): void
    {
        Schema::create('materia', function (Blueprint $table) {
            $table->string('codigo_mat', 20)->primary();
            $table->string('nombre_mat', 100);
            $table->integer('nivel')->nullable();
            $table->integer('horas_semanales')->nullable();
            $table->string('tipo', 40)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('materia');
    }
};
