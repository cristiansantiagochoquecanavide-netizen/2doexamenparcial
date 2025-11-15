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
        Schema::create('grupo_materia', function (Blueprint $table) {
            $table->string('codigo_mat', 20);
            $table->string('codigo_grupo', 20);
            
            $table->foreign('codigo_mat')
                ->references('codigo_mat')
                ->on('materia')
                ->onDelete('cascade');
                
            $table->foreign('codigo_grupo')
                ->references('codigo_grupo')
                ->on('grupo')
                ->onDelete('cascade');
                
            $table->primary(['codigo_mat', 'codigo_grupo']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grupo_materia');
    }
};
