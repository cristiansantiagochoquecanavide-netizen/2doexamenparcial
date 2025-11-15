<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Infraestructura;
use App\Models\Aula;

class CreateTestData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-test-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create test data for infraestructura and aulas';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Creando datos de prueba...');

        try {
            // Crear infraestructura
            $infraestructura = Infraestructura::firstOrCreate(
                ['nombre_infr' => 'Edificio Principal'],
                [
                    'ubicacion' => 'Piso 1',
                    'estado' => 'Activo'
                ]
            );

            $this->info("✓ Infraestructura creada: {$infraestructura->nombre_infr} (ID: {$infraestructura->id_infraestructura})");

            // Crear aulas
            $aulas = [
                ['nro_aula' => 'A-101', 'tipo' => 'Teórica', 'capacidad' => 40, 'estado' => 'Activo'],
                ['nro_aula' => 'A-102', 'tipo' => 'Práctica', 'capacidad' => 30, 'estado' => 'Activo'],
                ['nro_aula' => 'A-103', 'tipo' => 'Laboratorio', 'capacidad' => 25, 'estado' => 'Activo'],
            ];

            foreach ($aulas as $aula_data) {
                $aula_data['id_infraestructura'] = $infraestructura->id_infraestructura;
                
                $aula = Aula::firstOrCreate(
                    ['nro_aula' => $aula_data['nro_aula']],
                    $aula_data
                );

                $this->info("✓ Aula creada: {$aula->nro_aula}");
            }

            $this->info("\n✓ Datos de prueba creados exitosamente");
            return 0;
        } catch (\Exception $e) {
            $this->error("Error: {$e->getMessage()}");
            return 1;
        }
    }
}
