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
        Schema::create('infraestructura', function (Blueprint $table) {
            $table->id('id_infraestructura');
            $table->string('nombre_infr', 100);
            $table->string('ubicacion', 150)->nullable();
            $table->string('estado', 30)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('infraestructura');
    }
};
