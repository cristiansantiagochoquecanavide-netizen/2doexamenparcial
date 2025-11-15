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
        Schema::create('inasistencias', function (Blueprint $table) {
            $table->bigIncrements('id_inasistencia');
            $table->string('codigo_doc', 20);
            $table->date('fecha');
            $table->text('motivo_aparente')->nullable();
            $table->enum('estado', ['PENDIENTE', 'EN_REVISION', 'RESUELTA', 'RECHAZADA', 'CANCELADA'])->default('PENDIENTE');
            $table->enum('tipo_inasistencia', ['INJUSTIFICADA', 'PENDIENTE_JUSTIFICACION', 'JUSTIFICADA'])->default('INJUSTIFICADA');
            $table->timestamp('fecha_registro')->useCurrent();
            $table->timestamp('fecha_actualizacion')->useCurrent()->useCurrentOnUpdate();

            // Indexes
            $table->index('codigo_doc');
            $table->index('fecha');
            $table->index('estado');
            $table->index('tipo_inasistencia');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inasistencias');
    }
};
