<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Configurar el schema de PostgreSQL
        if (config('database.default') === 'pgsql') {
            $schema = config('database.connections.pgsql.search_path');
            
            // Crear el schema si no existe
            DB::statement("CREATE SCHEMA IF NOT EXISTS {$schema}");
            
            // Establecer el search_path
            DB::statement("SET search_path TO {$schema}");
            
            // Configurar el schema por defecto para migraciones
            Schema::defaultStringLength(255);
        }
    }
}
