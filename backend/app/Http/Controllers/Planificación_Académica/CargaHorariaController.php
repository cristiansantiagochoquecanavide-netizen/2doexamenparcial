<?php

namespace App\Http\Controllers\Planificación_Académica;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AsignacionHorario;

class CargaHorariaController extends Controller
{
    public function index(Request $request)
    {
        try {
            $cargaHoraria = AsignacionHorario::with(['docente', 'materia', 'grupo', 'aula'])
                ->where('estado', 'ACTIVO')
                ->get();
            
            return response()->json([
                'data' => $cargaHoraria,
                'message' => 'Carga horaria obtenida exitosamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener carga horaria',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($codigo)
    {
        try {
            $asignacion = AsignacionHorario::where('codigo', $codigo)
                ->with(['docente', 'materia', 'grupo', 'aula'])
                ->firstOrFail();
            
            return response()->json([
                'data' => $asignacion,
                'message' => 'Asignación obtenida exitosamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Asignación no encontrada',
                'error' => $e->getMessage()
            ], 404);
        }
    }
}
