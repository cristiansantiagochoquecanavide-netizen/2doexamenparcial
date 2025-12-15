<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Módulo: Gestión_de_Catálogos_Académicos
     */
    public function up(): void
    {
        Schema::create('docente', function (Blueprint $table) {
            $table->id('codigo_doc');
            $table->string('titulo', 100)->nullable();
            $table->string('correo_institucional', 100)->nullable();
            $table->integer('carga_horaria_max')->nullable();
            $table->unsignedBigInteger('id_usuario')->nullable();
            
            $table->foreign('id_usuario')
                ->references('id_usuario')
                ->on('usuario')
                ->onDelete('restrict');
        });
        
        // Agregar constraint CHECK usando DB::statement
        DB::statement('ALTER TABLE docente ADD CONSTRAINT chk_carga_horaria_positiva CHECK (carga_horaria_max >= 0)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('docente');
    }
};
