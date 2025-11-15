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
        Schema::create('resoluciones_inasistencias', function (Blueprint $table) {
            $table->bigIncrements('id_resolucion');
            $table->unsignedBigInteger('id_inasistencia')->unique();
            $table->enum('decision_final', ['APROBADA', 'RECHAZADA', 'PENDIENTE'])->default('PENDIENTE');
            $table->enum('tipo_accion', ['REPOSICION', 'AJUSTE', 'CONDONACION', 'NINGUNA'])->nullable();
            $table->text('descripcion_accion')->nullable();
            $table->timestamp('fecha_resolucion')->useCurrent();
            $table->unsignedBigInteger('id_usuario_coordinador');

            // Indexes
            $table->index('id_inasistencia');
            $table->index('decision_final');
            $table->index('id_usuario_coordinador');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resoluciones_inasistencias');
    }
};
