<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermisoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Permisos de P3 - Planificación Académica
        $permisos = [
            // P3 - Planificación Académica
            [
                'nombre' => 'asignar_carga_horaria',
                'descripcion' => 'Puede asignar carga horaria a docentes',
                'modulo' => 'P3',
            ],
            [
                'nombre' => 'ver_carga_horaria',
                'descripcion' => 'Puede ver la carga horaria asignada',
                'modulo' => 'P3',
            ],
            [
                'nombre' => 'editar_carga_horaria',
                'descripcion' => 'Puede editar carga horaria asignada',
                'modulo' => 'P3',
            ],
            [
                'nombre' => 'eliminar_carga_horaria',
                'descripcion' => 'Puede eliminar carga horaria asignada',
                'modulo' => 'P3',
            ],
            
            // P1 - Autenticación y Control de Acceso
            [
                'nombre' => 'gestionar_usuarios',
                'descripcion' => 'Puede crear, editar y eliminar usuarios',
                'modulo' => 'P1',
            ],
            [
                'nombre' => 'ver_usuarios',
                'descripcion' => 'Puede ver lista de usuarios',
                'modulo' => 'P1',
            ],
            [
                'nombre' => 'gestionar_roles',
                'descripcion' => 'Puede gestionar roles y permisos',
                'modulo' => 'P1',
            ],
            
            // P2 - Gestión de Catálogos Académicos
            [
                'nombre' => 'gestionar_docentes',
                'descripcion' => 'Puede gestionar docentes',
                'modulo' => 'P2',
            ],
            [
                'nombre' => 'gestionar_materias',
                'descripcion' => 'Puede gestionar materias',
                'modulo' => 'P2',
            ],
            [
                'nombre' => 'gestionar_grupos',
                'descripcion' => 'Puede gestionar grupos',
                'modulo' => 'P2',
            ],
            [
                'nombre' => 'gestionar_aulas',
                'descripcion' => 'Puede gestionar aulas',
                'modulo' => 'P2',
            ],
            [
                'nombre' => 'gestionar_infraestructura',
                'descripcion' => 'Puede gestionar infraestructura',
                'modulo' => 'P2',
            ],
            
            // P4 - Asistencia Docente
            [
                'nombre' => 'gestionar_asistencias',
                'descripcion' => 'Puede gestionar asistencias',
                'modulo' => 'P4',
            ],
            [
                'nombre' => 'ver_asistencias',
                'descripcion' => 'Puede ver asistencias',
                'modulo' => 'P4',
            ],
        ];

        // Insertar permisos
        foreach ($permisos as $permiso) {
            DB::table('carga_horaria.permiso')->updateOrInsert(
                ['nombre' => $permiso['nombre']],
                $permiso
            );
        }

        // Asignar permisos a roles
        $this->asignarPermisosAPerfiles();
    }

    private function asignarPermisosAPerfiles()
    {
        // Obtener IDs de roles
        $adminId = DB::table('carga_horaria.rol')->where('nombre', 'Administrador')->value('id_rol');
        $coordinadorId = DB::table('carga_horaria.rol')->where('nombre', 'Coordinador Academico')->value('id_rol');
        $docenteId = DB::table('carga_horaria.rol')->where('nombre', 'Docente')->value('id_rol');

        if ($adminId) {
            // Administrador tiene todos los permisos
            $permisos = DB::table('carga_horaria.permiso')->pluck('id_permiso');
            foreach ($permisos as $idPermiso) {
                DB::table('carga_horaria.rol_permiso')->updateOrInsert(
                    ['id_rol' => $adminId, 'id_permiso' => $idPermiso],
                    ['id_rol' => $adminId, 'id_permiso' => $idPermiso]
                );
            }
        }

        if ($coordinadorId) {
            // Coordinador Académico - Permisos de P3 principalmente
            $permisosCoordinador = DB::table('carga_horaria.permiso')
                ->whereIn('nombre', [
                    'asignar_carga_horaria',
                    'ver_carga_horaria',
                    'editar_carga_horaria',
                    'eliminar_carga_horaria',
                    'ver_usuarios',
                    'gestionar_docentes',
                    'gestionar_materias',
                    'gestionar_grupos',
                    'gestionar_aulas',
                    'ver_asistencias',
                ])
                ->pluck('id_permiso');

            foreach ($permisosCoordinador as $idPermiso) {
                DB::table('carga_horaria.rol_permiso')->updateOrInsert(
                    ['id_rol' => $coordinadorId, 'id_permiso' => $idPermiso],
                    ['id_rol' => $coordinadorId, 'id_permiso' => $idPermiso]
                );
            }
        }

        if ($docenteId) {
            // Docente - Permisos limitados
            $permisosDocente = DB::table('carga_horaria.permiso')
                ->whereIn('nombre', [
                    'ver_carga_horaria',
                    'ver_asistencias',
                ])
                ->pluck('id_permiso');

            foreach ($permisosDocente as $idPermiso) {
                DB::table('carga_horaria.rol_permiso')->updateOrInsert(
                    ['id_rol' => $docenteId, 'id_permiso' => $idPermiso],
                    ['id_rol' => $docenteId, 'id_permiso' => $idPermiso]
                );
            }
        }
    }
}
