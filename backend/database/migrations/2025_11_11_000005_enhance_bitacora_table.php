<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Verificar si la tabla bitacora existe
        if (!Schema::hasTable('bitacora')) {
            Schema::create('bitacora', function (Blueprint $table) {
                $table->id('id_bit');
                $table->string('modulo')->nullable();
                $table->string('accion');
                $table->text('descripcion')->nullable();
                $table->json('detalles_json')->nullable();
                $table->unsignedBigInteger('id_usuario')->nullable();
                $table->string('ip_address')->nullable();
                $table->text('user_agent')->nullable();
                $table->string('tabla_afectada')->nullable();
                $table->unsignedBigInteger('registro_id')->nullable();
                $table->timestamp('fecha_accion')->useCurrent();
                
                // Índices
                $table->index('id_usuario');
                $table->index('fecha_accion');
                $table->index('modulo');
                $table->index('accion');
            });
        } else {
            // Si ya existe, agregar columnas faltantes
            if (!Schema::hasColumn('bitacora', 'descripcion')) {
                Schema::table('bitacora', function (Blueprint $table) {
                    $table->text('descripcion')->nullable()->after('accion');
                });
            }
            if (!Schema::hasColumn('bitacora', 'detalles_json')) {
                Schema::table('bitacora', function (Blueprint $table) {
                    $table->json('detalles_json')->nullable()->after('descripcion');
                });
            }
            if (!Schema::hasColumn('bitacora', 'ip_address')) {
                Schema::table('bitacora', function (Blueprint $table) {
                    $table->string('ip_address')->nullable()->after('detalles_json');
                });
            }
            if (!Schema::hasColumn('bitacora', 'user_agent')) {
                Schema::table('bitacora', function (Blueprint $table) {
                    $table->text('user_agent')->nullable()->after('ip_address');
                });
            }
            if (!Schema::hasColumn('bitacora', 'tabla_afectada')) {
                Schema::table('bitacora', function (Blueprint $table) {
                    $table->string('tabla_afectada')->nullable()->after('user_agent');
                });
            }
            if (!Schema::hasColumn('bitacora', 'registro_id')) {
                Schema::table('bitacora', function (Blueprint $table) {
                    $table->unsignedBigInteger('registro_id')->nullable()->after('tabla_afectada');
                });
            }
        }
    }

    public function down()
    {
        if (Schema::hasTable('bitacora')) {
            Schema::dropIfExists('bitacora');
        }
    }
};
