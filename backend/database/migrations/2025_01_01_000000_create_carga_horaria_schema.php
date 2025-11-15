<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // El schema carga_horaria se crea desde AppServiceProvider.
        // No se debe crear schema aquí.
    }

    public function down(): void
    {
        // No se elimina schema.
    }
};
