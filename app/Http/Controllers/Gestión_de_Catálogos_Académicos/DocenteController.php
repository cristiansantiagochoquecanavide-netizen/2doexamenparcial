<?php

namespace App\Http\Controllers\Gestión_de_Catálogos_Académicos;

use App\Http\Controllers\Controller;
use App\Models\Docente;
use App\Models\Usuario;
use App\Models\Bitacora;
use Illuminate\Http\Request;

class DocenteController extends Controller
{
    /**
     * CU5: Gestionar Docentes - Listar
     */
    public function index(Request $request)
    {
        $docentes = Docente::with(['usuario.persona', 'usuario.rol'])
            ->when($request->search, function ($query, $search) {
                $query->whereHas('usuario.persona', function ($q) use ($search) {
                    $q->where('nombre', 'ILIKE', "%{$search}%")
                      ->orWhere('ci', 'ILIKE', "%{$search}%");
                });
            })
            ->when($request->titulo, function ($query, $titulo) {
                $query->where('titulo', 'ILIKE', "%{$titulo}%");
            })
            ->paginate($request->per_page ?? 15);

        return response()->json($docentes);
    }

    /**
     * CU5: Gestionar Docentes - Crear
     */
    public function store(Request $request)
    {
        $request->validate([
            'titulo' => 'nullable|string|max:100',
            'correo_institucional' => 'nullable|email|max:100',
            'carga_horaria_max' => 'nullable|integer|min:0',
            'id_usuario' => 'required|exists:usuario,id_usuario|unique:docente,id_usuario',
        ]);

        $docente = Docente::create($request->all());

        Bitacora::registrar('Gestión de Docentes', "Docente creado: {$docente->persona->nombre}");

        return response()->json([
            'message' => 'Docente creado exitosamente',
            'docente' => $docente->load('usuario.persona'),
        ], 201);
    }

    /**
     * CU5: Gestionar Docentes - Mostrar
     */
    public function show($id)
    {
        $docente = Docente::with([
            'usuario.persona',
            'usuario.rol',
            'asignaciones.grupo.materia',
            'asignaciones.horario',
            'asignaciones.aula'
        ])->findOrFail($id);

        return response()->json($docente);
    }

    /**
     * CU5: Gestionar Docentes - Actualizar
     */
    public function update(Request $request, $id)
    {
        $docente = Docente::findOrFail($id);

        $request->validate([
            'titulo' => 'nullable|string|max:100',
            'correo_institucional' => 'nullable|email|max:100',
            'carga_horaria_max' => 'nullable|integer|min:0',
        ]);

        $docente->update($request->all());

        Bitacora::registrar('Gestión de Docentes', "Docente actualizado: {$docente->persona->nombre}");

        return response()->json([
            'message' => 'Docente actualizado exitosamente',
            'docente' => $docente->load('usuario.persona'),
        ]);
    }

    /**
     * CU5: Gestionar Docentes - Eliminar
     */
    public function destroy($id)
    {
        $docente = Docente::findOrFail($id);
        $nombreDocente = $docente->persona->nombre;

        // Verificar si tiene asignaciones activas
        if ($docente->asignaciones()->where('estado', 'ACTIVO')->count() > 0) {
            return response()->json([
                'message' => 'No se puede eliminar el docente porque tiene asignaciones activas',
            ], 422);
        }

        $docente->delete();

        Bitacora::registrar('Gestión de Docentes', "Docente eliminado: {$nombreDocente}");

        return response()->json([
            'message' => 'Docente eliminado exitosamente',
        ]);
    }

    /**
     * Obtener carga horaria actual del docente
     */
    public function cargaHoraria($id, Request $request)
    {
        $docente = Docente::findOrFail($id);
        
        $periodo = $request->periodo_academico ?? now()->format('Y-1');
        
        $asignaciones = $docente->asignaciones()
            ->with(['horario', 'grupo.materia', 'aula'])
            ->where('periodo_academico', $periodo)
            ->where('estado', 'ACTIVO')
            ->get();

        $horasAsignadas = $asignaciones->sum(function ($asignacion) {
            return $asignacion->grupo->materia->horas_semanales ?? 0;
        });

        return response()->json([
            'docente' => $docente->load('usuario.persona'),
            'periodo_academico' => $periodo,
            'carga_horaria_max' => $docente->carga_horaria_max,
            'horas_asignadas' => $horasAsignadas,
            'horas_disponibles' => $docente->carga_horaria_max - $horasAsignadas,
            'asignaciones' => $asignaciones,
        ]);
    }
}
