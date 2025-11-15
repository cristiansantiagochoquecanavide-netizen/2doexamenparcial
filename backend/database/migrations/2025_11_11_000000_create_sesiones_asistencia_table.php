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
        Schema::create('sesiones_asistencia', function (Blueprint $table) {
            $table->id('id_sesion');
            $table->string('token', 255)->unique();
            $table->integer('id_asignacion');
            $table->timestamp('fecha_creacion')->useCurrent();
            $table->timestamp('fecha_expiracion');
            $table->enum('estado', ['ACTIVA', 'CERRADA', 'EXPIRADA'])->default('ACTIVA');
            $table->text('url_registro')->nullable();
            $table->longText('qr_data')->nullable(); // Almacenar QR en base64
            $table->timestamps();

            // Foreign key
            $table->foreign('id_asignacion')
                ->references('id_asignacion')
                ->on('asignacion_horario')
                ->onDelete('cascade');

            // Índices
            $table->index('token');
            $table->index('id_asignacion');
            $table->index('estado');
            $table->index('fecha_expiracion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sesiones_asistencia');
    }
};
