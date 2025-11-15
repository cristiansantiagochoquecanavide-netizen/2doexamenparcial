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
        // Tabla de permisos
        if (!Schema::hasTable('carga_horaria.permiso')) {
            Schema::create('carga_horaria.permiso', function (Blueprint $table) {
                $table->id('id_permiso');
                $table->string('nombre', 100)->unique();
                $table->string('descripcion', 255)->nullable();
                $table->string('modulo', 100); // P1, P2, P3, P4, etc.
                $table->timestamps();
            });
        }

        // Tabla de relación rol-permiso
        if (!Schema::hasTable('carga_horaria.rol_permiso')) {
            Schema::create('carga_horaria.rol_permiso', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('id_rol');
                $table->unsignedBigInteger('id_permiso');
                $table->timestamps();
                
                $table->foreign('id_rol')
                    ->references('id_rol')
                    ->on('carga_horaria.rol')
                    ->onDelete('cascade');
                
                $table->foreign('id_permiso')
                    ->references('id_permiso')
                    ->on('carga_horaria.permiso')
                    ->onDelete('cascade');
                
                $table->unique(['id_rol', 'id_permiso']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carga_horaria.rol_permiso');
        Schema::dropIfExists('carga_horaria.permiso');
    }
};
