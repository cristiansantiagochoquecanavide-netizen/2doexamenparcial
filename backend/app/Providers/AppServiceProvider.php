<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Ajuste seguro para PostgreSQL
        if (config('database.default') === 'pgsql') {

            Schema::defaultStringLength(255);

            // Leer el schema desde el ENV
            $schema = env('DB_SCHEMA');

            // Solo ejecutar si existe un nombre válido
            if (!empty($schema)) {
                try {
                    DB::statement("SET search_path TO {$schema}");
                } catch (\Exception $e) {
                    // Evitar romper la app en producción
                }
            }
        }
    }
}
