<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Autenticación_y_Control_de_Acceso\AuthController;
use App\Http\Controllers\Autenticación_y_Control_de_Acceso\UsuarioController;
use App\Http\Controllers\Autenticación_y_Control_de_Acceso\RolController;
use App\Http\Controllers\Gestión_de_Catálogos_Académicos\DocenteController;
use App\Http\Controllers\Gestión_de_Catálogos_Académicos\MateriaController;
use App\Http\Controllers\Gestión_de_Catálogos_Académicos\GrupoController;
use App\Http\Controllers\Gestión_de_Catálogos_Académicos\AulaController;
use App\Http\Controllers\Gestión_de_Catálogos_Académicos\InfraestructuraController;
use App\Http\Controllers\Planificación_Académica\AsignacionHorarioController;
use App\Http\Controllers\Planificación_Académica\HorarioController;
use App\Http\Controllers\Planificación_Académica\CargaHorariaController;
use App\Http\Controllers\Planificación_Académica\MallaHorariaController;
use App\Http\Controllers\Planificación_Académica\ConflictoHorarioController;
use App\Http\Controllers\Planificación_Académica\DisponibilidadAulasController;
use App\Http\Controllers\Asistencia_Docente\AsistenciaController;
use App\Http\Controllers\Asistencia_Docente\RegistroAsistenciaController;
use App\Http\Controllers\Asistencia_Docente\GestionInasistenciasController;
use App\Http\Controllers\Monitoreo_y_Reportes\DashboardController;
use App\Http\Controllers\Monitoreo_y_Reportes\ReportesController;
use App\Http\Controllers\Auditoria_y_Trazabilidad\BitacoraController;

// ==========================================
// RUTAS PÚBLICAS (sin autenticación)
// ==========================================
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/logout', [AuthController::class, 'logout']);
Route::post('/auth/refresh', [AuthController::class, 'refresh']);

// CU2: Ver usuarios (público)
Route::get('/usuarios', [UsuarioController::class, 'index']);

// CU11: Periodos académicos únicos para selects (público)
Route::get('periodos-academicos', [AsignacionHorarioController::class, 'periodosAcademicos']);

// ==========================================
// RUTAS DE LECTURA (solo lectura para combos y formularios)
// ==========================================
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/docentes', [DocenteController::class, 'index']);
    Route::get('/grupos', [GrupoController::class, 'index']);
    Route::get('/aulas', [AulaController::class, 'index']);
    Route::get('/horarios', [HorarioController::class, 'index']);
});

// ==========================================
// RUTAS PROTEGIDAS (con autenticación y autorización)
// ==========================================
Route::middleware('auth:sanctum')->group(function () {
    // Usuario autenticado
    // Endpoint para que el frontend obtenga el usuario autenticado con relaciones (AuthController@me)
    Route::get('/auth/me', [AuthController::class, 'me']);

    // Información básica del usuario (compatibilidad)
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Endpoint temporal de depuración: devuelve el usuario autenticado y los headers recibidos
    Route::get('/debug/whoami', function (Request $request) {
        $user = $request->user();
        if ($user) {
            $user = $user->load(['persona', 'rol.permisos']);
        }

        return response()->json([
            'user' => $user,
            'authorization_header' => $request->header('Authorization'),
            'all_headers' => $request->headers->all(),
        ]);
    });

    // ==========================================
    // P1: AUTENTICACIÓN Y CONTROL DE ACCESO
    // ==========================================
    
    // CU2: Gestionar Usuarios (operaciones protegidas)
    // Gestionar usuarios (con permiso)
    Route::middleware('permiso:gestionar_usuarios')->group(function () {
        Route::post('/usuarios', [UsuarioController::class, 'store']);
        Route::put('/usuarios/{usuario}', [UsuarioController::class, 'update']);
        Route::delete('/usuarios/{usuario}', [UsuarioController::class, 'destroy']);
        Route::get('/usuarios/{usuario}', [UsuarioController::class, 'show']);
    });
    
    // CU3: Gestionar Roles
    Route::middleware('permiso:gestionar_roles')->group(function () {
        Route::apiResource('roles', RolController::class);
    });

    // ==========================================
    // P2: GESTIÓN DE CATÁLOGOS ACADÉMICOS
    // ==========================================
    
    // CU4: Gestionar Docentes
    Route::middleware('permiso:gestionar_docentes')->group(function () {
        Route::apiResource('docentes', DocenteController::class);
    });
    
    // CU5: Gestionar Materias
    Route::middleware('permiso:gestionar_materias')->group(function () {
        Route::apiResource('materias', MateriaController::class);
    });
    
    // CU6: Gestionar Grupos
    Route::middleware('permiso:gestionar_grupos')->group(function () {
        Route::apiResource('grupos', GrupoController::class);
    });
    
    // CU7: Gestionar Aulas
    // Ruta de solo lectura para aulas (para combos y formularios, accesible a cualquier autenticado)
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/aulas', [AulaController::class, 'index']);
    });
    // Rutas protegidas para gestión de aulas (solo Administrador)
    Route::middleware(['auth:sanctum', 'permiso:gestionar_aulas', 'rol:Administrador'])->group(function () {
        Route::apiResource('aulas', AulaController::class)->except(['index']);
    });
    
    // CU8: Gestionar Infraestructura
    Route::middleware('permiso:gestionar_infraestructura')->group(function () {
        Route::apiResource('infraestructura', InfraestructuraController::class);
    });

    // ==========================================
    // P3: PLANIFICACIÓN ACADÉMICA
    // ==========================================
    


    // CU9: Ver Carga Horaria (sin restricción de rol)
    Route::get('carga-horaria', [CargaHorariaController::class, 'index']);
    Route::get('carga-horaria/{codigo}', [CargaHorariaController::class, 'show']);
    
    // CU10: Configurar Malla Horaria (Solo Coordinador Académico)
    Route::middleware('permiso:configurar_malla_horaria')->group(function () {
    Route::apiResource('malla-horaria', MallaHorariaController::class);
    Route::delete('malla-horaria-eliminar', [MallaHorariaController::class, 'eliminarMalla']);
    Route::post('malla-horaria/validar', [MallaHorariaController::class, 'validarFranjas']);
    });


    // CU11: Asignaciones de Horario - Asignar Carga Horaria (Solo Coordinador Académico)
    Route::middleware('permiso:asignar_carga_horaria')->group(function () {
        Route::apiResource('asignaciones', AsignacionHorarioController::class);
    });

    // Ver asignaciones (sin restricción)
    Route::get('asignaciones/docente/{codigo_doc}', [AsignacionHorarioController::class, 'horarioDocente']);
    Route::get('asignaciones/grupo/{codigo_grupo}', [AsignacionHorarioController::class, 'horarioGrupo']);

    // CU12: Gestionar Conflictos de Horario (Solo Coordinador Académico)
    Route::middleware('permiso:gestionar_conflictos_horario')->group(function () {
        Route::post('conflictos-horario/detectar', [ConflictoHorarioController::class, 'detectar']);
        Route::post('conflictos-horario/validar', [ConflictoHorarioController::class, 'validar']);
        Route::post('conflictos-horario/actualizar-estado', [ConflictoHorarioController::class, 'actualizarEstado']);
        Route::post('conflictos-horario/resolver', [ConflictoHorarioController::class, 'resolver']);
        Route::post('conflictos-horario/confirmar-resolucion', [ConflictoHorarioController::class, 'confirmarResolucion']);
        Route::post('conflictos-horario/notificar-resultado', [ConflictoHorarioController::class, 'notificarResultado']);
    });

    // CU13: Consultar disponibilidad de aulas (Solo Coordinador Académico)
    Route::middleware('permiso:consultar_disponibilidad_aulas')->group(function () {
        Route::post('disponibilidad-aulas/consultar-aulas', [DisponibilidadAulasController::class, 'consultarAulas']);
        Route::post('disponibilidad-aulas/solicitar-disponibilidad', [DisponibilidadAulasController::class, 'solicitarDisponibilidad']);
        Route::post('disponibilidad-aulas/consultar-estado', [DisponibilidadAulasController::class, 'consultarEstadoAulas']);
        Route::post('disponibilidad-aulas/aulas-disponibles', [DisponibilidadAulasController::class, 'aulasDisponibles']);
        Route::post('disponibilidad-aulas/registrar-consulta', [DisponibilidadAulasController::class, 'registrarConsulta']);
        Route::post('disponibilidad-aulas/confirmar-registro', [DisponibilidadAulasController::class, 'confirmarRegistro']);
        Route::post('disponibilidad-aulas/actualizar-disponibilidad', [DisponibilidadAulasController::class, 'actualizarDisponibilidad']);
        Route::post('disponibilidad-aulas/mostrar-resultados', [DisponibilidadAulasController::class, 'mostrarResultados']);
    });

    // ==========================================
    // P4: ASISTENCIA DOCENTE
    // ==========================================
    
    // CU13: Gestionar Asistencias
    Route::middleware('permiso:gestionar_asistencias')->group(function () {
        Route::apiResource('asistencias', AsistenciaController::class);
    });

    // CU14: Registrar Asistencia (Generar QR y Registrar)
    // Rutas públicas (sin autenticación) para escaneo de QR
    Route::post('/asistencia/generar-qr', [RegistroAsistenciaController::class, 'generarSesion'])->middleware('auth:sanctum');
    Route::get('/asistencia/registro/{token}', [RegistroAsistenciaController::class, 'mostrarFormulario'])->name('asistencia.registro.form');
    Route::post('/asistencia/registrar', [RegistroAsistenciaController::class, 'registrar']);
    
    // Rutas protegidas para gestión de sesiones
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/asistencia/sesiones-activas', [RegistroAsistenciaController::class, 'listarSesionesActivas']);
        Route::post('/asistencia/cerrar-sesion', [RegistroAsistenciaController::class, 'cerrarSesion']);
    });
    
    // Ver asistencias (sin restricción)
    Route::get('asistencias/docente/{codigo_doc}', [AsistenciaController::class, 'asistenciaDocente']);

    // CU15: Gestionar Inasistencias y Justificaciones (Solo Coordinador Académico)
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/inasistencias', [GestionInasistenciasController::class, 'listarPendientes']);
        Route::get('/inasistencias/{id}', [GestionInasistenciasController::class, 'mostrarDetalle']);
        Route::post('/inasistencias/{id}/revisar', [GestionInasistenciasController::class, 'revisar']);
        Route::get('/inasistencias/{id}/descargar', [GestionInasistenciasController::class, 'descargarJustificativo']);
    });

    // Subir justificativo (requiere autenticación)
    Route::post('/inasistencias/subir-justificativo', [GestionInasistenciasController::class, 'subirJustificativo'])->middleware('auth:sanctum');

    // ==========================================
    // P5: MONITOREO Y REPORTES
    // ==========================================
    
    // CU16: Visualizar Dashboard (Solo Administrador y Coordinador Académico)
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index']);
        Route::get('/dashboard/periodos', [DashboardController::class, 'getPeriodos']);
        
        // Funcionalidades adicionales implementadas pero no visibles en UI
        Route::get('/dashboard/kpis', [DashboardController::class, 'getKPIs']); // Control de KPIs
        Route::get('/dashboard/coordinacion', [DashboardController::class, 'getCoordinacionHorario']); // Coordinación de Horario
        Route::get('/dashboard/bitacora', [DashboardController::class, 'getBitacora']); // Acceso a Bitácora
    });

    // CU17: Generar Reportes PDF/Excel (Solo Administrador y Coordinador Académico)
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/reportes/generar', [ReportesController::class, 'generar']);
        Route::post('/reportes/compartir', [ReportesController::class, 'compartir']);
    });

    // ==========================================
    // P6: AUDITORÍA Y TRAZABILIDAD
    // ==========================================
    
    // CU18: Registrar y Consultar Bitácora de Acciones
    // NOTA: Temporalmente sin middleware auth:sanctum para debug
    Route::prefix('bitacora')->group(function () {
        // Solo Administrador y Auditor pueden ver bitácora
        Route::get('/', [BitacoraController::class, 'listarAcciones']);
        Route::get('/estadisticas', [BitacoraController::class, 'estadisticas']);
        Route::get('/modulos', [BitacoraController::class, 'obtenerModulos']);
        Route::get('/acciones', [BitacoraController::class, 'obtenerAcciones']);
        Route::get('/filtrar', [BitacoraController::class, 'filtrar']);
        Route::get('/{id}', [BitacoraController::class, 'obtenerDetalle']);
        Route::post('/exportar-csv', [BitacoraController::class, 'exportarCSV']);
        
        // Solo Administrador puede limpiar registros antiguos
        Route::middleware('permiso:gestionar_bitacora')->delete('/limpiar-antiguos', [BitacoraController::class, 'limpiarAntiguos']);
    });
});
