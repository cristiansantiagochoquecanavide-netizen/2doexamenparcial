<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Fix para Render (NO usar env() aquí)
        if (config('database.default') === 'pgsql') {

            // Siempre establecer longitud predeterminada
            Schema::defaultStringLength(255);

            // Si tienes schema:
            $schema = config('database.connections.pgsql.search_path');

            if ($schema) {
                try {
                    \DB::statement("SET search_path TO {$schema}");
                } catch (\Exception $e) {
                    // No romper nada
                }
            }
        }
    }
}
