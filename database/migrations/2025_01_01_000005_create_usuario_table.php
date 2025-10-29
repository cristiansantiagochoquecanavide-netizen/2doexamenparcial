<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Módulo: Autenticación_y_Control_de_Acceso
     */
    public function up(): void
    {
        Schema::create('usuario', function (Blueprint $table) {
            $table->id('id_usuario');
            $table->string('contrasena', 255);
            $table->boolean('estado')->default(true);
            $table->string('ci_persona', 20);
            $table->unsignedBigInteger('id_rol')->nullable();
            
            $table->foreign('ci_persona')
                ->references('ci')
                ->on('persona')
                ->onDelete('cascade');
                
            $table->foreign('id_rol')
                ->references('id_rol')
                ->on('rol')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usuario');
    }
};
