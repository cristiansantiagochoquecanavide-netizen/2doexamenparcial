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
        Schema::create('grupo', function (Blueprint $table) {
            $table->string('codigo_grupo', 20)->primary();
            $table->integer('capacidad_de_grupo')->nullable();
            $table->string('codigo_mat', 20)->nullable();
            
            $table->foreign('codigo_mat')
                ->references('codigo_mat')
                ->on('materia')
                ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grupo');
    }
};
