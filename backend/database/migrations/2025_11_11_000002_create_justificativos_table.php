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
        Schema::create('justificativos', function (Blueprint $table) {
            $table->bigIncrements('id_justificativo');
            $table->unsignedBigInteger('id_inasistencia');
            $table->string('archivo_ruta', 255);
            $table->string('archivo_nombre_original', 255);
            $table->string('archivo_tipo', 50); // PDF, DOC, JPG, etc.
            $table->integer('archivo_tamaño'); // en bytes
            $table->text('motivo_justificacion');
            $table->enum('estado_revision', ['EN_REVISION', 'PENDIENTE_VALIDACION', 'APROBADO', 'RECHAZADO'])->default('EN_REVISION');
            $table->text('comentario_revision')->nullable();
            $table->timestamp('fecha_carga')->useCurrent();
            $table->timestamp('fecha_revision')->nullable();

            // Indexes
            $table->index('id_inasistencia');
            $table->index('estado_revision');
            $table->index('fecha_carga');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('justificativos');
    }
};
