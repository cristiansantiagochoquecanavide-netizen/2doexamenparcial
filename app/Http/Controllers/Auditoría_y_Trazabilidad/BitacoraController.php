<?php

namespace App\Http\Controllers\Auditoría_y_Trazabilidad;

use App\Http\Controllers\Controller;
use App\Models\Bitacora;
use Illuminate\Http\Request;
use Carbon\Carbon;

class BitacoraController extends Controller
{
    /**
     * Listar registros de bitácora
     */
    public function index(Request $request)
    {
        $bitacoras = Bitacora::with('usuario.persona')
            ->when($request->modulo, function ($query, $modulo) {
                $query->where('modulo', 'ILIKE', "%{$modulo}%");
            })
            ->when($request->accion, function ($query, $accion) {
                $query->where('accion', 'ILIKE', "%{$accion}%");
            })
            ->when($request->id_usuario, function ($query, $idUsuario) {
                $query->where('id_usuario', $idUsuario);
            })
            ->when($request->fecha_inicio && $request->fecha_fin, function ($query) use ($request) {
                $query->whereBetween('fecha_accion', [$request->fecha_inicio, $request->fecha_fin]);
            })
            ->orderBy('fecha_accion', 'desc')
            ->paginate($request->per_page ?? 20);

        return response()->json($bitacoras);
    }

    /**
     * Mostrar registro de bitácora
     */
    public function show($id)
    {
        $bitacora = Bitacora::with('usuario.persona', 'usuario.rol')
            ->findOrFail($id);

        return response()->json($bitacora);
    }

    /**
     * Reporte de actividad por usuario
     */
    public function reporteUsuario($idUsuario, Request $request)
    {
        $fechaInicio = $request->fecha_inicio ?? Carbon::now()->startOfMonth();
        $fechaFin = $request->fecha_fin ?? Carbon::now()->endOfMonth();

        $bitacoras = Bitacora::with('usuario.persona')
            ->where('id_usuario', $idUsuario)
            ->whereBetween('fecha_accion', [$fechaInicio, $fechaFin])
            ->orderBy('fecha_accion', 'desc')
            ->get();

        $resumenPorModulo = $bitacoras->groupBy('modulo')->map(function ($items) {
            return [
                'total' => $items->count(),
                'acciones' => $items->pluck('accion')->unique()->values(),
            ];
        });

        return response()->json([
            'periodo' => [
                'inicio' => $fechaInicio,
                'fin' => $fechaFin,
            ],
            'total_acciones' => $bitacoras->count(),
            'resumen_por_modulo' => $resumenPorModulo,
            'bitacoras' => $bitacoras,
        ]);
    }

    /**
     * Reporte de actividad por módulo
     */
    public function reporteModulo($modulo, Request $request)
    {
        $fechaInicio = $request->fecha_inicio ?? Carbon::now()->startOfMonth();
        $fechaFin = $request->fecha_fin ?? Carbon::now()->endOfMonth();

        $bitacoras = Bitacora::with('usuario.persona')
            ->where('modulo', $modulo)
            ->whereBetween('fecha_accion', [$fechaInicio, $fechaFin])
            ->orderBy('fecha_accion', 'desc')
            ->get();

        $usuariosMasActivos = $bitacoras
            ->groupBy('id_usuario')
            ->map(function ($items) {
                return [
                    'usuario' => $items->first()->usuario,
                    'total_acciones' => $items->count(),
                ];
            })
            ->sortByDesc('total_acciones')
            ->take(10)
            ->values();

        return response()->json([
            'modulo' => $modulo,
            'periodo' => [
                'inicio' => $fechaInicio,
                'fin' => $fechaFin,
            ],
            'total_acciones' => $bitacoras->count(),
            'usuarios_mas_activos' => $usuariosMasActivos,
            'bitacoras' => $bitacoras,
        ]);
    }

    /**
     * Estadísticas generales de auditoría
     */
    public function estadisticas(Request $request)
    {
        $fechaInicio = $request->fecha_inicio ?? Carbon::now()->startOfMonth();
        $fechaFin = $request->fecha_fin ?? Carbon::now()->endOfMonth();

        $bitacoras = Bitacora::whereBetween('fecha_accion', [$fechaInicio, $fechaFin])->get();

        $estadisticas = [
            'total_acciones' => $bitacoras->count(),
            'acciones_por_modulo' => $bitacoras->groupBy('modulo')->map->count(),
            'acciones_por_dia' => $bitacoras->groupBy(function ($item) {
                return Carbon::parse($item->fecha_accion)->format('Y-m-d');
            })->map->count(),
            'usuarios_activos' => $bitacoras->pluck('id_usuario')->unique()->count(),
        ];

        return response()->json([
            'periodo' => [
                'inicio' => $fechaInicio,
                'fin' => $fechaFin,
            ],
            'estadisticas' => $estadisticas,
        ]);
    }
}
