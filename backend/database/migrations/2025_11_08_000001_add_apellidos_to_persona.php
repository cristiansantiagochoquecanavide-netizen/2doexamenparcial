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
        Schema::table('carga_horaria.persona', function (Blueprint $table) {
            // Agregar campos de apellidos si no existen
            if (!Schema::hasColumn('carga_horaria.persona', 'apellido_paterno')) {
                $table->string('apellido_paterno', 100)->nullable()->after('nombre');
            }
            if (!Schema::hasColumn('carga_horaria.persona', 'apellido_materno')) {
                $table->string('apellido_materno', 100)->nullable()->after('apellido_paterno');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('carga_horaria.persona', function (Blueprint $table) {
            if (Schema::hasColumn('carga_horaria.persona', 'apellido_paterno')) {
                $table->dropColumn('apellido_paterno');
            }
            if (Schema::hasColumn('carga_horaria.persona', 'apellido_materno')) {
                $table->dropColumn('apellido_materno');
            }
        });
    }
};
