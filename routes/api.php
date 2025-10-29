<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Controladores - Autenticación y Control de Acceso
use App\Http\Controllers\Autenticación_y_Control_de_Acceso\AuthController;
use App\Http\Controllers\Autenticación_y_Control_de_Acceso\UsuarioController;
use App\Http\Controllers\Autenticación_y_Control_de_Acceso\RolController;

// Controladores - Gestión de Catálogos Académicos
use App\Http\Controllers\Gestión_de_Catálogos_Académicos\DocenteController;
use App\Http\Controllers\Gestión_de_Catálogos_Académicos\MateriaController;
use App\Http\Controllers\Gestión_de_Catálogos_Académicos\GrupoController;
use App\Http\Controllers\Gestión_de_Catálogos_Académicos\InfraestructuraController;
use App\Http\Controllers\Gestión_de_Catálogos_Académicos\AulaController;

// Controladores - Planificación Académica
use App\Http\Controllers\Planificación_Académica\HorarioController;
use App\Http\Controllers\Planificación_Académica\AsignacionHorarioController;

// Controladores - Asistencia Docente
use App\Http\Controllers\Asistencia_Docente\AsistenciaController;

// Controladores - Auditoría y Trazabilidad
use App\Http\Controllers\Auditoría_y_Trazabilidad\BitacoraController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// ==========================================
// RUTAS PÚBLICAS (Sin autenticación)
// ==========================================

Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
});

// ==========================================
// RUTAS PROTEGIDAS (Requieren autenticación)
// ==========================================

Route::middleware('auth:sanctum')->group(function () {

    // ==========================================
    // AUTENTICACIÓN Y CONTROL DE ACCESO
    // ==========================================
    
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/cambiar-contrasena', [AuthController::class, 'cambiarContrasena']);
    });

    // Usuarios
    Route::apiResource('usuarios', UsuarioController::class);
    Route::post('usuarios/{id}/toggle-estado', [UsuarioController::class, 'toggleEstado']);

    // Roles y Permisos
    Route::apiResource('roles', RolController::class);
    Route::get('permisos', [RolController::class, 'listarPermisos']);
    Route::post('roles/{id}/permisos', [RolController::class, 'asignarPermisos']);

    // ==========================================
    // GESTIÓN DE CATÁLOGOS ACADÉMICOS
    // ==========================================

    // Docentes
    Route::apiResource('docentes', DocenteController::class);
    Route::get('docentes/{id}/carga-horaria', [DocenteController::class, 'cargaHoraria']);

    // Materias
    Route::apiResource('materias', MateriaController::class);
    Route::get('materias/nivel/{nivel}', [MateriaController::class, 'porNivel']);

    // Grupos
    Route::apiResource('grupos', GrupoController::class);
    Route::post('grupos/{codigo}/materias', [GrupoController::class, 'asignarMaterias']);

    // Infraestructura
    Route::apiResource('infraestructuras', InfraestructuraController::class);

    // Aulas
    Route::apiResource('aulas', AulaController::class);
    Route::post('aulas/{nro_aula}/verificar-disponibilidad', [AulaController::class, 'verificarDisponibilidad']);

    // ==========================================
    // PLANIFICACIÓN ACADÉMICA
    // ==========================================

    // Horarios
    Route::apiResource('horarios', HorarioController::class);
    Route::get('horarios/dia/{dia}', [HorarioController::class, 'porDia']);
    Route::get('horarios/turno/{turno}', [HorarioController::class, 'porTurno']);

    // Asignaciones de Horario
    Route::apiResource('asignaciones', AsignacionHorarioController::class);
    Route::get('asignaciones/docente/{codigo_doc}', [AsignacionHorarioController::class, 'horarioDocente']);
    Route::get('asignaciones/grupo/{codigo_grupo}', [AsignacionHorarioController::class, 'horarioGrupo']);

    // ==========================================
    // ASISTENCIA DOCENTE
    // ==========================================

    Route::apiResource('asistencias', AsistenciaController::class);
    Route::post('asistencias/registrar-hoy', [AsistenciaController::class, 'registrarHoy']);
    Route::get('asistencias/reporte/docente/{codigo_doc}', [AsistenciaController::class, 'reporteDocente']);
    Route::get('asistencias/reporte/grupo/{codigo_grupo}', [AsistenciaController::class, 'reporteGrupo']);

    // ==========================================
    // AUDITORÍA Y TRAZABILIDAD
    // ==========================================

    Route::prefix('bitacora')->group(function () {
        Route::get('/', [BitacoraController::class, 'index']);
        Route::get('/{id}', [BitacoraController::class, 'show']);
        Route::get('/reporte/usuario/{id_usuario}', [BitacoraController::class, 'reporteUsuario']);
        Route::get('/reporte/modulo/{modulo}', [BitacoraController::class, 'reporteModulo']);
        Route::get('/estadisticas/general', [BitacoraController::class, 'estadisticas']);
    });
});
