<?php

namespace App\Http\Controllers\Auditoria_y_Trazabilidad;

use App\Http\Controllers\Controller;
use App\Models\Bitacora;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BitacoraController extends Controller
{
    /**
     * Listar todas las acciones registradas en bitácora con paginación
     */
    public function listarAcciones(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 50);
            $usuario = $request->get('usuario');
            $modulo = $request->get('modulo');
            $accion = $request->get('accion');
            $fecha_desde = $request->get('fecha_desde');
            $fecha_hasta = $request->get('fecha_hasta');
            $buscar = $request->get('buscar');

            $query = Bitacora::with('usuario')
                ->latest('fecha_accion');

            // Filtro por usuario
            if ($usuario) {
                $query->whereHas('usuario', function ($q) use ($usuario) {
                    $q->where('id_usuario', $usuario)
                        ->orWhere('nombre_usuario', 'ilike', "%{$usuario}%")
                        ->orWhere('email', 'ilike', "%{$usuario}%");
                });
            }

            // Filtro por módulo
            if ($modulo) {
                $query->where('modulo', 'ilike', "%{$modulo}%");
            }

            // Filtro por acción
            if ($accion) {
                $query->where('accion', 'ilike', "%{$accion}%");
            }

            // Filtro por rango de fechas
            if ($fecha_desde && $fecha_hasta) {
                $desde = Carbon::parse($fecha_desde)->startOfDay();
                $hasta = Carbon::parse($fecha_hasta)->endOfDay();
                $query->whereBetween('fecha_accion', [$desde, $hasta]);
            } elseif ($fecha_desde) {
                $desde = Carbon::parse($fecha_desde)->startOfDay();
                $query->where('fecha_accion', '>=', $desde);
            } elseif ($fecha_hasta) {
                $hasta = Carbon::parse($fecha_hasta)->endOfDay();
                $query->where('fecha_accion', '<=', $hasta);
            }

            // Búsqueda general en descripción y detalles
            if ($buscar) {
                $query->where(function ($q) use ($buscar) {
                    $q->where('descripcion', 'ilike', "%{$buscar}%")
                        ->orWhere('detalles_json', 'ilike', "%{$buscar}%");
                });
            }

            $bitacoras = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $bitacoras->items(),
                'pagination' => [
                    'total' => $bitacoras->total(),
                    'per_page' => $bitacoras->perPage(),
                    'current_page' => $bitacoras->currentPage(),
                    'last_page' => $bitacoras->lastPage(),
                    'from' => $bitacoras->firstItem(),
                    'to' => $bitacoras->lastItem(),
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al listar bitácora',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener detalles completos de una acción registrada
     */
    public function obtenerDetalle($id)
    {
        try {
            $bitacora = Bitacora::with('usuario')->findOrFail($id);

            // Decodificar JSON si existe
            $detalles = is_string($bitacora->detalles_json) 
                ? json_decode($bitacora->detalles_json, true) 
                : $bitacora->detalles_json;

            return response()->json([
                'success' => true,
                'data' => array_merge($bitacora->toArray(), [
                    'detalles_json' => $detalles,
                    'fecha_accion_formateada' => $bitacora->fecha_accion->format('d/m/Y H:i:s'),
                    'usuario_nombre' => $bitacora->usuario?->nombre_usuario ?? 'N/A'
                ])
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Bitácora no encontrada',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Filtro avanzado con estadísticas
     */
    public function filtrar(Request $request)
    {
        try {
            $tipo_filtro = $request->get('tipo', 'todos'); // todos, hoy, semana, mes
            $limite = $request->get('limite', 100);

            $query = Bitacora::latest('fecha_accion');

            switch ($tipo_filtro) {
                case 'hoy':
                    $query->whereDate('fecha_accion', today());
                    break;
                case 'semana':
                    $query->whereBetween('fecha_accion', [
                        now()->startOfWeek(),
                        now()->endOfWeek()
                    ]);
                    break;
                case 'mes':
                    $query->whereMonth('fecha_accion', now()->month)
                        ->whereYear('fecha_accion', now()->year);
                    break;
            }

            $bitacoras = $query->limit($limite)->get();

            // Calcular estadísticas
            $estadisticas = [
                'total' => $bitacoras->count(),
                'por_modulo' => $bitacoras->groupBy('modulo')->map->count(),
                'por_accion' => $bitacoras->groupBy('accion')->map->count(),
                'por_usuario' => $bitacoras->groupBy('id_usuario')->count(),
                'ips_unicas' => $bitacoras->groupBy('ip_address')->count(),
            ];

            return response()->json([
                'success' => true,
                'data' => $bitacoras,
                'estadisticas' => $estadisticas,
                'periodo' => $tipo_filtro
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al filtrar bitácora',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Estadísticas generales del sistema
     */
    public function estadisticas()
    {
        try {
            $ahora = now();
            
            $stats = [
                'total_acciones' => Bitacora::count(),
                'acciones_hoy' => Bitacora::whereDate('fecha_accion', today())->count(),
                'acciones_semana' => Bitacora::whereBetween('fecha_accion', [
                    now()->startOfWeek(),
                    now()->endOfWeek()
                ])->count(),
                'usuarios_activos_hoy' => Bitacora::whereDate('fecha_accion', today())
                    ->distinct('id_usuario')->count(),
                'modulos_mas_usados' => Bitacora::select('modulo')
                    ->groupBy('modulo')
                    ->selectRaw('modulo, count(*) as total')
                    ->orderByRaw('total DESC')
                    ->limit(10)
                    ->get(),
                'acciones_mas_comunes' => Bitacora::select('accion')
                    ->groupBy('accion')
                    ->selectRaw('accion, count(*) as total')
                    ->orderByRaw('total DESC')
                    ->limit(10)
                    ->get(),
                'ultimas_acciones' => Bitacora::with('usuario')
                    ->latest('fecha_accion')
                    ->limit(10)
                    ->get(),
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estadísticas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Exportar bitácora a CSV
     */
    public function exportarCSV(Request $request)
    {
        try {
            $fecha_desde = $request->get('fecha_desde');
            $fecha_hasta = $request->get('fecha_hasta');
            $modulo = $request->get('modulo');

            $query = Bitacora::with('usuario');

            if ($fecha_desde && $fecha_hasta) {
                $desde = Carbon::parse($fecha_desde)->startOfDay();
                $hasta = Carbon::parse($fecha_hasta)->endOfDay();
                $query->whereBetween('fecha_accion', [$desde, $hasta]);
            }

            if ($modulo) {
                $query->where('modulo', $modulo);
            }

            $bitacoras = $query->orderBy('fecha_accion', 'desc')->get();

            // Generar CSV
            $csv = "ID,Usuario,Módulo,Acción,Descripción,Fecha y Hora,IP Address,Tabla Afectada,Registro ID\n";
            
            foreach ($bitacoras as $bit) {
                $csv .= sprintf(
                    "%d,%s,%s,%s,\"%s\",%s,%s,%s,%s\n",
                    $bit->id_bit,
                    $bit->usuario?->nombre_usuario ?? 'Sistema',
                    $bit->modulo,
                    $bit->accion,
                    str_replace('"', '""', $bit->descripcion),
                    $bit->fecha_accion->format('d/m/Y H:i:s'),
                    $bit->ip_address ?? 'N/A',
                    $bit->tabla_afectada ?? 'N/A',
                    $bit->registro_id ?? 'N/A'
                );
            }

            return response($csv, 200)
                ->header('Content-Type', 'text/csv; charset=utf-8')
                ->header('Content-Disposition', 'attachment; filename="bitacora_' . now()->format('Y-m-d_His') . '.csv"');

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al exportar bitácora',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Limpiar registros antiguos (más de 90 días)
     */
    public function limpiarAntiguos(Request $request)
    {
        try {
            $dias = $request->get('dias', 90);
            $fecha_limite = now()->subDays($dias);

            $eliminados = Bitacora::where('fecha_accion', '<', $fecha_limite)->delete();

            return response()->json([
                'success' => true,
                'message' => "Se eliminaron {$eliminados} registros de bitácora más antiguos a {$dias} días",
                'registros_eliminados' => $eliminados
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al limpiar bitácora',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener módulos únicos registrados en bitácora
     */
    public function obtenerModulos()
    {
        try {
            $modulos = Bitacora::distinct('modulo')
                ->pluck('modulo')
                ->sort()
                ->values();

            return response()->json([
                'success' => true,
                'data' => $modulos
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener módulos',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener acciones únicas registradas en bitácora
     */
    public function obtenerAcciones()
    {
        try {
            $acciones = Bitacora::distinct('accion')
                ->pluck('accion')
                ->sort()
                ->values();

            return response()->json([
                'success' => true,
                'data' => $acciones
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener acciones',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
