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
        Schema::create('aula', function (Blueprint $table) {
            $table->string('nro_aula', 20)->primary();
            $table->string('tipo', 40)->nullable();
            $table->integer('capacidad')->nullable();
            $table->string('estado', 30)->nullable();
            $table->unsignedBigInteger('id_infraestructura');
            
            $table->foreign('id_infraestructura')
                ->references('id_infraestructura')
                ->on('infraestructura')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aula');
    }
};
